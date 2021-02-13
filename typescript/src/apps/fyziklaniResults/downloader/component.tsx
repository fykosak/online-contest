import { ResponseData } from '@apps/fyziklaniResults/downloader/inferfaces';
import { NetteActions } from '@appsCollector/netteActions';
import { dispatchFetch } from '@fetchApi/netteFetch';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { FyziklaniResultsCoreStore } from '../shared/reducers/coreStore';
import { lang } from '@i18n/i18n';

interface StateProps {
    error: Error | any;
    isSubmitting: boolean;
    lastUpdated: string;
    refreshDelay: number;
    isRefreshing: boolean;
    actions: NetteActions;
}

interface DispatchProps {
    onWaitForFetch(delay: number, url: string): void;
}

interface OwnProps {
    data: ResponseData;
}

class Downloader extends React.Component<DispatchProps & StateProps & OwnProps, {}> {

    public componentDidUpdate(prevProps: DispatchProps & StateProps & OwnProps) {
        const {lastUpdated: oldLastUpdated, refreshDelay, onWaitForFetch} = this.props;
        if (oldLastUpdated !== prevProps.lastUpdated) {

            if (refreshDelay) {
                const url = this.props.actions.getAction('refresh');
                onWaitForFetch(refreshDelay, url);
            }
        }
    }

    public render() {
        const {lastUpdated, isRefreshing, isSubmitting, error} = this.props;
        return (
            <div className="last-update-info bg-white">
                <i
                    title={error ? (error.status + ' ' + error.statusText) : lastUpdated}
                    className={isRefreshing ? 'text-success fa fa-check' : 'text-danger fa fa-exclamation-triangle'}/>
                {isSubmitting && (<i className="fa fa-spinner fa-spin"/>)}
                <span>{lang.getText('Last updated:') + ' ' + (new Date(lastUpdated)).toLocaleTimeString()}</span>
            </div>
        );
    }
}

const mapStateToProps = (state: FyziklaniResultsCoreStore): StateProps => {
    return {
        actions: state.fetchApi.actions,
        error: state.fetchApi.error,
        isRefreshing: state.downloader.isRefreshing,
        isSubmitting: state.fetchApi.submitting,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onWaitForFetch: (delay: number, url: string): number => setTimeout(() => {
            return dispatchFetch<ResponseData>(url, dispatch, null);
        }, delay),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);
