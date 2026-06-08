import { Delta } from 'quill';

const now =
  Date.now ||
  function () {
    return new Date().getTime();
  };

export const MCS_QUILL_DELTA_PREFIX = 'MCS_QUILL_DELTA:v1:';

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

export function renderMarkdown(markdown) {
  return window.md.render(markdown);
}

// Markdown table parsing utilities

/**
 * Parses a GitHub-style markdown table into header metadata and cell rows.
 * @param {string} markdown markdown text containing a table
 * @returns {{ rows: string[][], headerRow: string[]|null, hasHeaderRow: boolean }} parsed table data
 */
export function parseMarkdownTable(markdown) {
  const emptyTable = { rows: [], headerRow: null, hasHeaderRow: false };

  if (!markdown) {
    return emptyTable;
  }

  const lines = markdown
    .replace(/\r\n?/g, '\n')
    .split('\n')
    .map((line) => line.trim())
    .filter((line) => line.includes('|'));

  const separatorIndex = lines.findIndex(isMarkdownTableSeparator);

  if (separatorIndex <= 0) {
    return emptyTable;
  }

  const headerRow = parseMarkdownTableRow(lines[separatorIndex - 1]);
  const rows = lines
    .slice(separatorIndex + 1)
    .filter((line) => !isMarkdownTableSeparator(line))
    .map(parseMarkdownTableRow)
    .filter((row) => !row.every((cell) => cell === ''));

  return {
    rows,
    headerRow,
    hasHeaderRow: headerRow.some((cell) => cell !== ''),
  };
}

/**
 * Checks whether a markdown line is the required table separator row.
 * @param {string} line possible markdown table separator line
 * @returns {boolean} true when every cell is a valid separator marker
 */
function isMarkdownTableSeparator(line) {
  const cells = parseMarkdownTableRow(line);

  return (
    cells.length > 0 &&
    cells.every((cell) => /^:?-{3,}:?$/.test(cell.replace(/\s/g, '')))
  );
}

/**
 * Splits one markdown table row into trimmed cells, preserving escaped pipes.
 * @param {string} line markdown table row text
 * @returns {string[]} parsed cell values
 */
function parseMarkdownTableRow(line) {
  const cells = [];
  let cell = '';

  for (let index = 0; index < line.length; index += 1) {
    const character = line[index];

    if (character === '\\' && line[index + 1] === '|') {
      cell += '|';
      index += 1;
      continue;
    }

    if (character === '|') {
      cells.push(cell.trim());
      cell = '';
      continue;
    }

    cell += character;
  }

  cells.push(cell.trim());

  if (cells[0] === '') cells.shift();
  if (cells[cells.length - 1] === '') cells.pop();

  return cells;
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
  const lines = markdownText.replace(/\r\n?/g, '\n').split('\n');
  let markdownDelta = new Delta();

  for (let index = 0; index < lines.length; index += 1) {
    const tableBlock = getMarkdownTableBlock(lines, index);

    if (tableBlock) {
      markdownDelta = deltaAddMarkdownTable(
        markdownDelta,
        parseMarkdownTable(tableBlock.markdown),
      );
      index = tableBlock.endIndex;
      continue;
    }

    markdownDelta = addMarkdownInline(markdownDelta, lines[index]);
    markdownDelta = markdownDelta.insert('\n');
  }

  return delta.concat(markdownDelta);
}

/**
 * @param {Delta} delta existing delta
 * @param {{ rows: string[][], headerRow: string[]|null, hasHeaderRow: boolean }} table parsed markdown table
 * @returns A new delta with the table rows added as key/value blocks
 */
export function deltaAddMarkdownTable(delta, table) {
  if (!table?.rows?.length) {
    return delta;
  }

  if (isTwoColumnHeaderTable(table)) {
    return delta.concat(buildTwoColumnMarkdownTableDelta(table));
  }

  const rowGroups = table.hasHeaderRow
    ? table.rows
        .filter((row) => !row.every((cell) => cell === ''))
        .map((row) =>
          table.headerRow
            .map((key, index) => ({
              key,
              value: row[index] || '',
            }))
            .filter(({ key }) => key),
        )
        .filter((group) => group.length)
    : table.rows
        .filter((row) => !row.every((cell) => cell === ''))
        .map((row) => [
          {
            key: row[0] || '',
            value: row.slice(1).filter(Boolean).join(' '),
          },
        ])
        .filter((group) => group[0].key);

  return rowGroups.reduce((parsedDelta, group, groupIndex) => {
    group.forEach(({ key, value }) => {
      parsedDelta = deltaAddMarkdownProperty(parsedDelta, key, value);
    });

    if (groupIndex < rowGroups.length - 1) {
      parsedDelta = parsedDelta.insert('\n');
    }

    return parsedDelta;
  }, delta);
}

/**
 * Determines whether a parsed table should use the compact two-column layout.
 * @param {{ rows: string[][], headerRow: string[]|null, hasHeaderRow: boolean }} table parsed markdown table
 * @returns {boolean} true when the table has exactly two headers and two-column rows
 */
function isTwoColumnHeaderTable(table) {
  if (!table.hasHeaderRow || table.headerRow.length !== 2) {
    return false;
  }

  return table.rows.every((row) => row.length <= 2);
}

/**
 * Builds a compact markdown-style Delta for two-column tables.
 * @param {{ rows: string[][], headerRow: string[] }} table parsed two-column table
 * @returns {Delta} Delta containing a bold header line followed by row value lines
 */
function buildTwoColumnMarkdownTableDelta(table) {
  let tableDelta = addMarkdownTableLine(new Delta(), table.headerRow, {
    bold: true,
  });

  table.rows
    .filter((row) => !row.every((cell) => cell === ''))
    .forEach((row) => {
      tableDelta = addMarkdownTableLine(tableDelta, [
        row[0] || '',
        row[1] || '',
      ]);
    });

  return tableDelta;
}

/**
 * Adds one markdown-parsed pipe-separated line to a Delta.
 * @param {Delta} delta existing delta
 * @param {string[]} cells cell text to render on the line
 * @param {Object} attributes attributes applied to the whole line
 * @returns {Delta} Delta with the rendered line appended
 */
function addMarkdownTableLine(delta, cells, attributes = {}) {
  cells.forEach((cell, index) => {
    if (index > 0) {
      delta = delta.insert(' | ', attributes);
    }

    delta = addMarkdownInline(delta, cell, attributes);
  });

  return delta.insert('\n');
}

/**
 * Adds a markdown-parsed key/value property line to a Delta.
 * @param {Delta} delta existing delta
 * @param {string} propertyName markdown text for the bold property name
 * @param {string} propertyValue markdown text for the property value
 * @returns {Delta} Delta with the rendered property line appended
 */
function deltaAddMarkdownProperty(delta, propertyName, propertyValue) {
  let property = addMarkdownInline(new Delta(), propertyName, { bold: true });

  if (propertyValue) {
    property = property.insert(':', { bold: true }).insert(' ');
    property = addMarkdownInline(property, propertyValue);
  }

  return delta.concat(property.insert('\n'));
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

/**
 * Finds the closing markdown emphasis delimiter for an opening delimiter.
 * @param {string} text full markdown line being parsed
 * @param {string} delimiter opening delimiter, such as *, **, or ***
 * @param {number} startIndex index where the opening delimiter begins
 * @returns {number} closing delimiter index, or -1 when no close exists
 */
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

/**
 * Inserts buffered plain text with the current markdown attributes.
 * @param {Delta} markdownDelta delta being built
 * @param {string} text buffered text to insert
 * @param {Object} attributes active Quill attributes
 * @returns {Delta} Delta with the text inserted, or unchanged for empty text
 */
function insertMarkdownText(markdownDelta, text, attributes) {
  if (!text) return markdownDelta;

  const activeAttributes = Object.keys(attributes).length
    ? { ...attributes }
    : undefined;

  return markdownDelta.insert(text, activeAttributes);
}

/**
 * Finds a contiguous markdown table block starting at a header line.
 * @param {string[]} lines normalized markdown lines
 * @param {number} startIndex candidate table header line index
 * @returns {{ markdown: string, endIndex: number }|null} table block metadata, or null when no table starts here
 */
function getMarkdownTableBlock(lines, startIndex) {
  const separatorIndex = startIndex + 1;

  if (
    !lines[startIndex]?.includes('|') ||
    !lines[separatorIndex] ||
    !isMarkdownTableSeparator(lines[separatorIndex])
  ) {
    return null;
  }

  let endIndex = separatorIndex;

  while (lines[endIndex + 1]?.includes('|')) {
    endIndex += 1;
  }

  const markdown = lines.slice(startIndex, endIndex + 1).join('\n');
  const table = parseMarkdownTable(markdown);

  if (!table.rows.length) {
    return null;
  }

  return { markdown, endIndex };
}

/**
 * Parses inline markdown emphasis into Delta inserts.
 * @param {Delta} markdownDelta delta being built
 * @param {string} text inline markdown text
 * @param {Object} attributes active Quill attributes inherited from parent parsing
 * @returns {Delta} Delta with parsed inline content appended
 */
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

    if (delimiter.length >= 2) {
      inlineAttributes.bold = true;
    }

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
