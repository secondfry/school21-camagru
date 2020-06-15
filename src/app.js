import './style.pcss';
import { initConsole } from './utilities/console';
import { initPageCapture } from './components/capture';
import { initPageRegister } from './components/register';

/**
 * In production should be set to false.
 * This will suppress console output (this is sadly enforced by task subject).
 */
console.isDevelopmentBuild = true;

initConsole();

const PAGE_CAPTURE = '?action=view&page=create';
const PAGE_REGISTER = '?action=view&page=register';

switch (window.location.search) {
  case PAGE_CAPTURE:
    initPageCapture();
    break;
  case PAGE_REGISTER:
    initPageRegister();
    break;
}
