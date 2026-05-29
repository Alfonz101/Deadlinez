// Client-side task filtering for the Dashboard and Archive.
// Combines the optional text search with the chip filters in one pass — all in
// the browser, no server request. Cards carry data-* attributes (data-priority,
// data-due, data-status) that each .filter-group targets via its data-filter.

const searchInput = document.getElementById('search');
const filterBar   = document.getElementById('filters');

if (searchInput || filterBar) {

    // state: filter group name -> selected value. 'all' means no constraint.
    const state = {};

    if (filterBar) {
        filterBar.querySelectorAll('.filter-group').forEach(group => {
            state[group.dataset.filter] = 'all';
        });

        // One delegated listener for every chip.
        filterBar.addEventListener('click', function (e) {
            const chip = e.target.closest('.chip');
            if (!chip) return;

            const group = chip.closest('.filter-group');
            state[group.dataset.filter] = chip.dataset.value;

            // Move the active marker within this group only.
            group.querySelectorAll('.chip').forEach(function (c) {
                c.classList.toggle('is-active', c === chip);
            });

            apply();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', apply);
    }

    function apply() {
        const query = (searchInput ? searchInput.value : '').toLowerCase();
        let visible = 0;

        document.querySelectorAll('.task-card').forEach(function (card) {
            const matchesText = !query ||
                (card.dataset.title || '').toLowerCase().includes(query);

            // Every active filter group must match this card's matching data-* value.
            const matchesChips = Object.keys(state).every(function (key) {
                return state[key] === 'all' || card.dataset[key] === state[key];
            });

            const show = matchesText && matchesChips;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Friendly note when nothing survives the filters.
        const note = document.getElementById('no-match');
        if (note) note.hidden = visible > 0;
    }
}
