import { startStimulusApp } from '@symfony/stimulus-bridge';
import LogController from "./controllers/LogController";
import ServiceController from "./controllers/ServiceController";

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));

// register any custom, 3rd party controllers here
app.register('log', LogController);
app.register('service', ServiceController);
