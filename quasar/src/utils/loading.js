import { Loading, QSpinnerGears } from 'quasar';

let loadingStartTime;

export function showLoading(message) {
  loadingStartTime = Date.now(); // Record the time when loading starts
  Loading.show({
    spinner: QSpinnerGears,
    message: message
  });
}

export function hideLoading() {
  const elapsedTime = Date.now() - loadingStartTime; // Calculate elapsed time
  const minDuration = 1000; // Minimum duration in milliseconds (1.0 seconds)

  // Ensure the loading screen stays for at least 1.5 seconds
  if (elapsedTime < minDuration) {
    setTimeout(() => {
      Loading.hide();
    }, minDuration - elapsedTime);
  } else {
    Loading.hide();
  }
}
