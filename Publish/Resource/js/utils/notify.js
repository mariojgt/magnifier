import Toastify from 'toastify-js';

export const toast = (text, type = 'info', duration = 3500) => {
  const colors = {
    info: '#3b82f6',
    success: '#10B981',
    warning: '#f59e0b',
    error: '#EF4444'
  };
  Toastify({ text, duration, position: 'top-right', style: { background: colors[type] || colors.info } }).showToast();
};

export const toastSuccess = (text) => toast(text, 'success');
export const toastWarning = (text) => toast(text, 'warning');
export const toastError = (text) => toast(text, 'error');

// Show friendly messages for HTTP errors coming from Laravel APIs
export function showHttpError(error, fallback = 'Something went wrong. Please try again.') {
  // Network error with no response
  if (!error?.response) {
    if (error?.message && /network|timeout/i.test(error.message)) {
      toastError('Network error. Check your connection and try again.');
    } else {
      toastError(fallback);
    }
    return;
  }

  const { status, data } = error.response;
  const msg = (typeof data === 'string') ? data : (data?.message || fallback);

  // 413 Payload Too Large (enhanced by backend with details)
  if (status === 413) {
    let detail = '';
    if (data?.detail?.content_length && data?.detail?.server_limit) {
      detail = ` (${data.detail.content_length} > ${data.detail.server_limit})`;
    } else if (data?.detail?.dimensions && data?.detail?.max_pixels) {
      detail = ` (${data.detail.dimensions}, limit ~${data.detail.max_pixels.toLocaleString()} px)`;
    }
    toastError(`${msg}${detail}`.trim());
    return;
  }

  // 422 Validation errors
  if (status === 422) {
    if (data?.errors && typeof data.errors === 'object') {
      Object.values(data.errors).flat().forEach((e) => toastError(String(e)));
    } else {
      toastError(msg || 'Validation failed.');
    }
    return;
  }

  if (status === 404) {
    toastWarning('Resource not found. It may have been moved or deleted.');
    return;
  }
  if (status === 401 || status === 403) {
    toastWarning('You don\'t have permission to perform this action.');
    return;
  }

  if (status >= 500) {
    toastError(`Server error: ${msg}`);
    return;
  }

  toastError(msg || fallback);
}
