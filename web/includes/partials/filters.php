<?php
// Client-side filter chips, driven by app.js.
// Set $show_status = true before including to add the Open/Done group (archive).
// Each .filter-group's data-filter must match a data-* attribute on .task-card.
?>
<div class="filters" id="filters">

    <div class="filter-group" data-filter="priority">
        <button type="button" class="chip is-active" data-value="all">All</button>
        <button type="button" class="chip" data-value="high"><span class="dot dot-high"></span>High</button>
        <button type="button" class="chip" data-value="medium"><span class="dot dot-medium"></span>Med</button>
        <button type="button" class="chip" data-value="low"><span class="dot dot-low"></span>Low</button>
    </div>

    <div class="filter-group" data-filter="due">
        <button type="button" class="chip is-active" data-value="all">All</button>
        <button type="button" class="chip" data-value="overdue">Overdue</button>
        <button type="button" class="chip" data-value="soon">Soon</button>
        <button type="button" class="chip" data-value="later">Later</button>
    </div>

    <?php if (!empty($show_status)): ?>
    <div class="filter-group" data-filter="status">
        <button type="button" class="chip is-active" data-value="all">All</button>
        <button type="button" class="chip" data-value="open">Open</button>
        <button type="button" class="chip" data-value="done">Done</button>
    </div>
    <?php endif; ?>

</div>
