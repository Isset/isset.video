import React from 'react';
import ReactDOM from 'react-dom';
import {archiveAjax, wpAjax} from '../../ajax';
import VideoItem from './video-item';
import Pagination from './pagination';
import AdminVideoDetails from './admin-video-details';

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
        const {root} = window.IssetVideoArchiveAjax;

        archiveAjax(`api/folders/${root}/files`, {offset, limit, q: search, orderBy, orderDirection}).then(result => {
            const {offset, results, total} = result;

            if (results) {
                this.setState({offset, results, total, checked: []});
            }
        });
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
        const list = checked ? results.map(result => result.file.uuid) : [];

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
        if (confirm('Are you sure you want to delete the selected videos?')) {
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

        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        this.searchTimeout = setTimeout(() => this.setState({offset: 0, search: value}), 500);
    };

    render() {
        const {offset, results, total, limit, uuid, checkAll, orderDirection} = this.state;
        const {adminUrl} = window.IssetVideoPublisherAjax;

        return <div>
            {uuid && <AdminVideoDetails uuid={uuid} onClose={this.onCloseDetails} />}
            <div className="video-publisher-flex video-publisher-flex-between">
                <div>
                    <a className="isset-video-btn isset-video-upload-btn" href={`${adminUrl}admin.php?page=isset-video-upload`}>
                        <span className="dashicons dashicons-plus-alt"></span> Upload New
                    </a>
                    <button className="isset-video-btn btn-danger isset-overview-delete" onClick={this.deleteChecked}>
                        <span className="dashicons dashicons-trash" /> Delete Selected
                    </button>
                    <input className="isset-video-search-input" placeholder="Search" value={this.search} onChange={this.changeSearch} />
                </div>

                <Pagination onNavigate={this.loadVideos} total={total} limit={limit} offset={offset} />
            </div>
            <table className="iv-w-100 isset-video-overview-table">
                <thead>
                    <tr className="iv-t-align-left">
                        <th className="isset-video-table-spacer">
                            <input type="checkbox" checked={checkAll} onChange={this.toggleCheckAll} />
                        </th>
                        <th className="isset-video-thumbnail-th">Thumbnail</th>
                        <th>Duration</th>
                        <th>Size</th>
                        <th className="isset-video-pointer" onClick={() => this.sortBy('filename', orderDirection)}>Filename</th>
                        <th className="isset-video-pointer" onClick={() => this.sortBy('dateCreated', orderDirection)}>Created</th>
                    </tr>
                </thead>
                <tbody>
                    {results.map((result) => {
                        const {uuid, filename, date_created, duration, preview, width, height, stills} = result.file;

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
                </tbody>
            </table>
            <div className="video-publisher-flex video-publisher-flex-end">
                <Pagination onNavigate={this.loadVideos} total={total} limit={limit} offset={offset} />
            </div>
        </div>;
    }
}

window.addEventListener('load', () => {
    if (typeof adminpage !== 'undefined' && adminpage === 'toplevel_page_isset-video-overview') {
        const issetVideoOverviewContainer = document.getElementById('isset-video-overview-container');
        ReactDOM.render(<IssetVideoOverview />, issetVideoOverviewContainer);
    }
});