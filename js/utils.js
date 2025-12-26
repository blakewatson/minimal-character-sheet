const now =
  Date.now ||
  function () {
    return new Date().getTime();
  };

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
