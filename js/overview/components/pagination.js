import React from 'react';
import PropTypes from 'prop-types';

class Pagination extends React.Component {
    static propTypes = {
        onNavigate: PropTypes.func.isRequired,
        total: PropTypes.number.isRequired,
        limit: PropTypes.number.isRequired,
        offset: PropTypes.number.isRequired,
    };

    calculateCurrentPage = (limit, offset) => {
        return offset > 0 ? offset / limit + 1 : 1;
    };

    calculateTotalPages = (limit, total) => {
        return Math.ceil(total / limit);
    };

    getActivePages = (currentPage, totalPages) => {
        const pages = [];

        const start = currentPage > 3 ? currentPage - 2 : 1;

        for (let i = start; i < start + 5 && i <= totalPages; i++) {
            pages.push(i);
        }

        return pages;
    };


    navigate = (event) => {
        event.preventDefault();

        const {page} = event.target.dataset;
        const {limit, onNavigate} = this.props;

        onNavigate((parseInt(page) - 1) * limit, limit);
    };

    render() {
        const {total, limit, offset} = this.props;
        const current = this.calculateCurrentPage(limit, offset);
        const totalPages = this.calculateTotalPages(limit, total);
        const activePages = this.getActivePages(current, totalPages);

        return <nav aria-label="pagination">
            <ul className="isset-video-pagination mb-0">
                {current > 4 && <li className="page-item"><a className="page-link" href="#" data-page={1} onClick={this.navigate}>&laquo;</a></li>}
                {current > 1 && <li className="page-item"><a className="page-link" href="#" data-page={current - 1} onClick={this.navigate}>&lsaquo;</a></li>}

                {activePages.map(page => <li className={`page-item ${current === page && 'active'}`} key={`pagebtn-${page}`}><a className="page-link" data-page={page} href="#" onClick={this.navigate}>{page}</a></li>)}

                {current < totalPages && <li className="page-item"><a className="page-link" href="#" data-page={current + 1} onClick={this.navigate}>&rsaquo;</a></li>}
                {current < totalPages && <li className="page-item"><a className="page-link" href="#" data-page={totalPages} onClick={this.navigate}>&raquo;</a></li>}
            </ul>
        </nav>;

    }
}

export default Pagination;
