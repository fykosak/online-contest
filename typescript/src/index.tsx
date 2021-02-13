import FyziklaniResultsTable from '@apps/fyziklaniResults/table';
import { appsCollector } from '@appsCollector/index';
import { mapRegister } from '@appsCollector/mapRegister';
import * as React from 'react';

mapRegister.registerActionsComponent('score-list', FyziklaniResultsTable);

appsCollector.run();
