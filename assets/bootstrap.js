import { Application  } from '@hotwired/stimulus';
import { startStimulusApp } from '@symfony/stimulus-bridge';

const application = Application.start();
const app = startStimulusApp(require.context('./controllers', true, /\.js$/));
