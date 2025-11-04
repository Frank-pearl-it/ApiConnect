import { Loading, QSpinnerGears } from 'quasar';

let loadingStartTime;

export function showLoading(message) {
  try {
    loadingStartTime = Date.now();
    if (Loading) {
      Loading.show({
        spinner: QSpinnerGears,
        message
      });
    }
  } catch (err) {
    // Silently ignore if app context is gone
    console.warn('Loading.show() failed — app likely unmounted:', err);
  }
}

export function hideLoading() {
  try {
    const elapsedTime = Date.now() - (loadingStartTime || 0);
    const minDuration = 1000;

    const doHide = () => {
      if (Loading) Loading.hide();
    };

    if (elapsedTime < minDuration) {
      setTimeout(doHide, minDuration - elapsedTime);
    } else {
      doHide();
    }
  } catch (err) {
    console.warn('Loading.hide() failed — app likely unmounted:', err);
  }
}
