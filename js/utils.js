import { Delta } from 'quill';

const now =
  Date.now ||
  function () {
    return new Date().getTime();
  };

export const MCS_QUILL_DELTA_PREFIX = 'MCS_QUILL_DELTA:v1:';

export const DISALLOWED_QUILL_EMBEDS = ['image'];

export function removeDisallowedEmbedsFromDelta(delta) {
  if (!delta || !Array.isArray(delta.ops)) {
    return delta;
  }

  return {
    ...delta,
    ops: delta.ops
      .filter((op) => {
        if (!op || typeof op.insert !== 'object' || op.insert === null) {
          return true;
        }

        return !DISALLOWED_QUILL_EMBEDS.some((embed) =>
          Object.prototype.hasOwnProperty.call(op.insert, embed),
        );
      })
      .map((op) => ({ ...op })),
  };
}

// from underscore.js, modified to use optional trailingWait
export function throttle(func, wait, options = {}) {
  let timeout = null;
  let context, args, result;
  let previous = 0;
  let lastCallTime = 0;

  const trailingWait = options.trailingWait ?? wait;

  const later = () => {
    const sinceLastCall = now() - lastCallTime;

    if (sinceLastCall >= trailingWait) {
      previous = options.leading === false ? 0 : now();
      timeout = null;
      result = func.apply(context, args);
      if (!timeout) context = args = null;
    } else {
      // Wait a bit more if they typed again during the trailing wait
      timeout = setTimeout(later, trailingWait - sinceLastCall);
    }
  };

  const throttled = function () {
    const _now = now();
    if (!previous && options.leading === false) previous = _now;

    const remaining = wait - (_now - previous);
    context = this;
    args = arguments;
    lastCallTime = _now;

    if (remaining <= 0 || remaining > wait) {
      if (timeout) {
        clearTimeout(timeout);
        timeout = null;
      }
      previous = _now;
      result = func.apply(context, args);
      if (!timeout) context = args = null;
    } else if (!timeout && options.trailing !== false) {
      timeout = setTimeout(later, trailingWait);
    }

    return result;
  };

  return throttled;
}

export const signedNumString = (num) => {
  num = parseInt(num);
  if (num > 0) return `+${num}`;
  return num.toString();
};

export function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

export function replaceUnderscores(str) {
  return str.replace(/_/g, ' ');
}

export async function copyHtmlToClipboard(htmlContent, plainTextContent) {
  try {
    // 1. Create a Blob for the HTML version
    const htmlBlob = new Blob([htmlContent], { type: 'text/html' });

    // 2. Create a Blob for the plain text version (highly recommended fallback)
    const textBlob = new Blob([plainTextContent], { type: 'text/plain' });

    // 3. Create a ClipboardItem with both formats
    // The keys are the MIME types
    const item = new ClipboardItem({
      'text/html': htmlBlob,
      'text/plain': textBlob,
    });

    // 4. Write the item to the clipboard
    return navigator.clipboard.write([item]);
  } catch (err) {
    console.error('Failed to copy: ', err);
  }
}

// QuillJS delta manipulation utilities

/**
 * @param {Delta} delta existing delta
 * @param {string} text the text to be bolded
 * @returns A new delta with the bolded line added to the original delta
 */
export function deltaAddBoldedLine(delta, text) {
  const newLine = new Delta().insert(text + '\n', { bold: true });
  return delta.concat(newLine);
}

/**
 * @param {Delta} delta existing delta
 * @param {string} headerText the text to be added as a header
 * @param {number} level the header level
 * @returns A new delta with the header added to the original delta
 */
export function deltaAddHeader(delta, headerText, level = 1) {
  const header = new Delta().insert(headerText + '\n', { header: level });
  return delta.concat(header);
}

/**
 * @param {Delta} delta existing delta
 * @param {string} text the text to be italicized
 * @returns A new delta with the italicized line added to the original delta
 */
export function deltaAddItalicizedLine(delta, text) {
  const newLine = new Delta().insert(text + '\n', { italic: true });
  return delta.concat(newLine);
}

/**
 * @param {Delta} delta existing delta
 * @param {string} text the text to be added as a line
 * @returns A new delta with the line added to the original delta
 */
export function deltaAddLine(delta, text) {
  const newLine = new Delta().insert(text + '\n');
  return delta.concat(newLine);
}

/**
 * @param {Delta} delta existing delta
 * @param {string} lineText the text to be added as an ordered list item
 * @returns A new delta with the ordered list item added to the original delta
 */
export function deltaAddListOrdered(delta, lineText) {
  const newLine = new Delta().insert(lineText + '\n', { list: 'ordered' });
  return delta.concat(newLine);
}

/**
 * @param {Delta} delta existing delta
 * @param {string} lineText the text to be added as a bullet list item
 * @returns A new delta with the bullet list item added to the original delta
 */
export function deltaAddListBullet(delta, lineText) {
  const newLine = new Delta().insert(lineText + '\n', { list: 'bullet' });
  return delta.concat(newLine);
}

/**
 * A basic parser that only supports bold, italics, and line breaks.
 * @param {Delta} delta existing delta
 * @param {string} markdownText the markdown text to be added
 * @returns A new delta with the markdown text added to the original delta
 */
export function deltaAddMarkdown(delta, markdownText) {
  const markdownDelta = markdownText
    .replace(/\r\n?/g, '\n')
    .split('\n')
    .reduce((parsedDelta, line) => {
      parsedDelta = addMarkdownInline(parsedDelta, line);
      return parsedDelta.insert('\n');
    }, new Delta());

  return delta.concat(markdownDelta);
}

/**
 * @param {Delta} delta existing delta
 * @param {string} propertyName the bolded property name
 * @param {string} propertyValue the property value
 * @param {string|null} listType optional list type for the property line
 * @returns A new delta with the property added to the original delta
 */
export function deltaAddProperty(
  delta,
  propertyName,
  propertyValue,
  listType = null,
) {
  let property = new Delta().insert(
    `${propertyName}${propertyValue ? ':' : ''}`,
    { bold: true },
  );

  if (listType && propertyValue) {
    property = property.insert(` ${propertyValue}\n`, { list: listType });
  } else if (listType) {
    property = property.insert('\n', { list: listType });
  } else if (propertyValue) {
    property = property.insert(` ${propertyValue}\n`);
  } else {
    property = property.insert('\n');
  }

  return delta.concat(property);
}

// Helper functions to parse markdown

function findClosingDelimiter(text, delimiter, startIndex) {
  const delimiterCharacter = delimiter[0];
  let closeIndex = startIndex;

  while (closeIndex !== -1) {
    closeIndex = text.indexOf(delimiter, closeIndex + delimiter.length);

    if (closeIndex === -1 || delimiter.length > 1) {
      return closeIndex;
    }

    const previousCharacter = text[closeIndex - 1];
    const nextCharacter = text[closeIndex + delimiter.length];

    if (
      previousCharacter !== delimiterCharacter &&
      nextCharacter !== delimiterCharacter
    ) {
      return closeIndex;
    }
  }

  return -1;
}

function insertMarkdownText(markdownDelta, text, attributes) {
  if (!text) return markdownDelta;

  const activeAttributes = Object.keys(attributes).length
    ? { ...attributes }
    : undefined;

  return markdownDelta.insert(text, activeAttributes);
}

function addMarkdownInline(markdownDelta, text, attributes = {}) {
  let buffer = '';
  let index = 0;

  const flushBuffer = () => {
    markdownDelta = insertMarkdownText(markdownDelta, buffer, attributes);
    buffer = '';
  };

  while (index < text.length) {
    const remainingText = text.slice(index);
    const escapedCharacter = remainingText.match(/^\\([\\*_])/);

    if (escapedCharacter) {
      buffer += escapedCharacter[1];
      index += escapedCharacter[0].length;
      continue;
    }

    const delimiter = ['***', '___', '**', '__', '*', '_'].find(
      (possibleDelimiter) =>
        remainingText.startsWith(possibleDelimiter) &&
        findClosingDelimiter(text, possibleDelimiter, index) !== -1,
    );

    if (!delimiter) {
      buffer += text[index];
      index += 1;
      continue;
    }

    const closeIndex = findClosingDelimiter(text, delimiter, index);
    const innerText = text.slice(index + delimiter.length, closeIndex);
    const inlineAttributes = { ...attributes };

    flushBuffer();

    if (delimiter.length >= 2) inlineAttributes.bold = true;
    if (delimiter.length === 1 || delimiter.length === 3) {
      inlineAttributes.italic = true;
    }

    markdownDelta = addMarkdownInline(
      markdownDelta,
      innerText,
      inlineAttributes,
    );
    index = closeIndex + delimiter.length;
  }

  flushBuffer();

  return markdownDelta;
}
