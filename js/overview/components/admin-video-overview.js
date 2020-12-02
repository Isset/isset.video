import React from 'react';
import ReactDOM from 'react-dom';
import {archiveAjax, wpAjax} from '../../ajax';
import VideoItem from './video-item';
import Pagination from './pagination';
import AdminVideoDetails from './admin-video-details';
import {__} from '@wordpress/i18n'
import VideoUpload from './video-upload';

class IssetVideoOverview extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            offset: 0,
            total: 0,
            limit: 25,
            results: [],
            uuid: '',
            search: '',
            checkAll: false,
            checked: [],
            orderBy: 'dateCreated',
            orderDirection: 'desc',
            showUpload: false,
        };

        this.search = '';
        this.searchTimeout = null;
    }

    componentDidMount() {
        this.init();
    }

    componentDidUpdate(prevProps, prevState) {
        const {search, offset, orderBy, orderDirection} = this.state;

        if (prevState.search !== search || prevState.orderBy !== orderBy || prevState.orderDirection !== orderDirection || prevState.offset !== offset) {
            this.loadVideos(offset);
        }
    }

    init = () => {
        this.loadVideos(0)
    };

    loadVideos = (offset) => {
        const {search, limit, orderBy, orderDirection} = this.state;

        archiveAjax(`api/search`, {offset, limit, q: search, orderBy, orderDirection}).then(result => {
            const {offset, results, total} = result;

            if (results) {
                this.setState({offset, results, total, checked: []});
            }
        });
    };

    refresh = () => {
        const {offset} = this.state;
        this.loadVideos(offset);
    };

    onSelect = (uuid) => {
        this.setState({uuid});
    };

    onCloseDetails = () => {
        this.setState({uuid: ''});
    };

    toggleCheckAll = event => {
        const {checked} = event.target;
        const {results} = this.state;

        const list = checked ? results.map(result => result.uuid) : [];

        this.setState({checkAll: checked, checked: list});
    }

    isVideoChecked = uuid => this.state.checked.includes(uuid);

    toggleChecked = (uuid, check) => {
        const {checked} = this.state;

        if (check) {
            this.setState({checked: [...checked, uuid]});
        } else {
            checked.splice(checked.indexOf(uuid), 1);
            this.setState({checked: [...checked]});
        }
    };

    sortBy = (type, direction) => {
        const {orderBy} = this.state;

        // If type is the same, toggle direction
        if (type === orderBy) {
            const newDirection = direction === 'asc' ? 'desc' : 'asc';
            this.setState({'orderDirection': newDirection});
        } else  {
            this.setState({'orderBy': type});
        }
    }

    deleteChecked = () => {
        if (confirm(__('Are you sure you want to delete the selected videos?', 'isset-video-publisher'))) {
            const {checked} = this.state;

            archiveAjax(
                'api/files/batch-delete', {}, 'POST', {files: checked}
                ).then(() => {
                    const {limit, offset, search} = this.state;

                    this.loadVideos(offset);
                }
            );
        }
    }

    changeSearch = event => {
        const {value} = event.target;
        this.search = value;

        this.setState({search: this.search}, () => {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            this.searchTimeout = setTimeout(() => this.setState({offset: 0, search: value}), 500);
        });
    };

    clearSearch = () => {
        this.search = '';
        this.setState({offset: 0, search: ''});
    };

    showUploadDialog = showUpload => this.setState({showUpload});

    render() {
        const {offset, results, total, limit, uuid, checkAll, orderDirection, showUpload} = this.state;
        const {adminUrl} = window.IssetVideoPublisherAjax;

        return <div>
            {uuid && <AdminVideoDetails uuid={uuid} onClose={this.onCloseDetails} />}
            <div className="video-publisher-flex video-publisher-flex-between video-publisher-mb-2">
                <div>
                    <button className="isset-video-btn isset-video-upload-btn" onClick={() => this.showUploadDialog(true)}>
                        <span className="dashicons dashicons-plus-alt" /> {__('Upload New', 'isset-video-publisher')}
                    </button>
                    <button className="isset-video-btn btn-danger isset-overview-delete" onClick={this.deleteChecked}>
                        <span className="dashicons dashicons-trash" /> {__('Delete Selected', 'isset-video-publisher')}
                    </button>
                    <div className="isset-video-search-container">
                        <input className="isset-video-search-input" placeholder={__('Search', 'isset-video-publisher')} value={this.search} onChange={this.changeSearch} />
                        {!this.search && <span className="dashicons dashicons-search" />}
                        {this.search && <span className="dashicons dashicons-no-alt isset-video-clear" onClick={this.clearSearch} />}
                    </div>
                </div>
                <div>
                    <span className="video-publisher-inline-block video-publisher-mr-2 video-publisher-text-white">{__('Total results', 'isset-video-publisher')}: {total}</span>
                    <Pagination onNavigate={this.loadVideos} total={total} limit={limit} offset={offset} />
                </div>
            </div>
            <table className="iv-w-100 isset-video-overview-table">
                <thead>
                    <tr className="iv-t-align-left">
                        <th className="isset-video-table-spacer">
                            <input type="checkbox" checked={checkAll} onChange={this.toggleCheckAll} />
                        </th>
                        <th className="isset-video-thumbnail-th">{__('Thumbnail', 'isset-video-publisher')}</th>
                        <th>{__('Duration', 'isset-video-publisher')}</th>
                        <th>{__('Size', 'isset-video-publisher')}</th>
                        <th className="isset-video-pointer isset-video-filename-header" onClick={() => this.sortBy('filename', orderDirection)}>{__('Filename', 'isset-video-publisher')}</th>
                        <th className="isset-video-pointer" onClick={() => this.sortBy('dateCreated', orderDirection)}>{__('Created', 'isset-video-publisher')}</th>
                    </tr>
                </thead>
                <tbody>
                    {results.map((result) => {
                        const {uuid, filename, date_created, duration, preview, width, height, stills} = result;

                        return <VideoItem
                            key={`isset-video-item-${uuid}`}
                            uuid={uuid}
                            filename={filename}
                            created={date_created}
                            duration={duration}
                            preview={preview}
                            size={width ? `${width}x${height}` : ''}
                            stills={stills}
                            onSelect={this.onSelect}
                            checked={this.isVideoChecked(uuid)}
                            onCheck={this.toggleChecked}
                        />
                    })}
                    {results.length === 0 && <tr className="iv-v-align-top">
                        <td colSpan={6} className="video-publisher-p-2">
                            {__('No publishes found', 'isset-video-publisher')}
                        </td>
                    </tr>}
                </tbody>
            </table>
            <div className="video-publisher-flex video-publisher-flex-end">
                <Pagination onNavigate={this.loadVideos} total={total} limit={limit} offset={offset} />
            </div>
            <VideoUpload show={showUpload} toggleShow={this.showUploadDialog} refresh={this.refresh} />
        </div>;
    }
}

export default IssetVideoOverview;

window.addEventListener('load', () => {
    if (typeof adminpage !== 'undefined' && adminpage === 'toplevel_page_isset-video-overview') {
        const {loggedIn} = window.IssetVideoPublisherAjax;

        if (loggedIn) {
            const issetVideoOverviewContainer = document.getElementById('isset-video-overview-container');
            ReactDOM.render(<IssetVideoOverview />, issetVideoOverviewContainer);
        }
    }
});