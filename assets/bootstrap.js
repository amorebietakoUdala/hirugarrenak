import { startStimulusApp } from '@symfony/stimulus-bundle';
import { locale } from '@amorebietakoudala/stimulus-controller-bundle/src/locale_controller';
import { table } from '@amorebietakoudala/stimulus-controller-bundle/src/table_controller';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register('locale', locale );
app.register('table', table );
