import { Submit, Task, Team } from '../../fyziklani/helpers/interfaces';

export interface ResponseData {
    availablePoints: number[];
    basePath: string;
    gameStart: string;
    gameEnd: string;
    times: {
        toStart: number;
        toEnd: number;
        visible: boolean;
    };
    lastUpdated: string;
    isOrg: boolean;
    refreshDelay: number;
    submits: Submit[];
    teams?: Team[];
    tasks?: Task[];
    categories?: string[];
}
