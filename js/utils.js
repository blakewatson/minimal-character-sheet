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
