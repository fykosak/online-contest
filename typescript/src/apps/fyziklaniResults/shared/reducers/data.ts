import { ACTION_FETCH_SUCCESS, ActionFetchSuccess } from '@fetchApi/actions';
import { Response2 } from '@fetchApi/interfaces';
import {
    Submit,
    Task,
    Team,
} from '@apps/fyziklani/helpers/interfaces';
import { ResponseData } from '../../downloader/inferfaces';

export interface State {
    submits?: Submit[];
    tasks?: Task[];
    teams?: Team[];
    categories?: string[];
    availablePoints?: number[];
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<ResponseData>>): State => {
    const {submits, tasks, teams, categories, availablePoints} = action.data.data;
    return {
        ...state,
        availablePoints: availablePoints.map((value) => +value),
        categories: categories ? categories : state.categories,
        submits: {
            ...state.submits,
            ...submits,
        },
        tasks: tasks ? tasks : state.tasks,
        teams: teams ? teams : state.teams,
    };
};

export const fyziklaniData = (state: State = {}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};