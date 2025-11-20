import { startStimulusApp } from '@symfony/stimulus-bridge';
import { locale } from '@amorebietakoudala/stimulus-controller-bundle/src/locale_controller';
import { table } from '@amorebietakoudala/stimulus-controller-bundle/src/table_controller';

export const app = startStimulusApp(
    require.context('@symfony/stimulus-bridge/lazy-controller-loader!./controllers', true, /\.(j|t)sx?$/)
);

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register('locale', locale );
app.register('table', table );
