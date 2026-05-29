<?php

// e() — always use this when printing user data into HTML.
// It converts characters like < > " into harmless text so they can't run as code.
// Example: e('<script>') outputs "&lt;script&gt;" — just text, not real HTML.
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


// format_due() — turn a stored due_date into a scannable, urgency-graded chip.
// Returns null when there's no date (caller renders nothing), otherwise:
//   ['label' => human text, 'class' => overdue|soon|later, 'icon' => lucide name]
// Urgency is graded so only genuinely pressing deadlines light up with color.
function format_due(?string $due): ?array
{
    if ($due === null || $due === '') {
        return null;
    }

    $today = new DateTimeImmutable('today');
    $date  = new DateTimeImmutable($due);
    $days  = (int) $today->diff($date)->format('%r%a'); // signed: negative = past

    if ($days < 0) {
        $n = abs($days);
        return ['label' => "Overdue {$n}d", 'class' => 'overdue', 'icon' => 'alert-triangle'];
    }
    if ($days === 0) {
        return ['label' => 'Due today', 'class' => 'soon', 'icon' => 'clock'];
    }
    if ($days <= 2) {
        return ['label' => "Due in {$days}d", 'class' => 'soon', 'icon' => 'clock'];
    }

    return ['label' => $date->format('j M'), 'class' => 'later', 'icon' => 'calendar-days'];
}


// action_form() — render one task action as a tiny one-button POST form.
// Complete, reopen and delete all post to action.php with the same three
// hidden fields, so we build that form here once instead of repeating it.
//   $id      — task the action applies to
//   $action  — 'complete' | 'reopen' | 'delete' (action.php dispatches on this)
//   $back    — page to return to afterwards ('index.php' | 'archive.php')
//   $icon    — Lucide icon name for the button
//   $label   — tooltip / accessible title
//   $css     — extra CSS class (e.g. 'btn-delete')
//   $confirm — when true, ask before submitting (used for delete)
function action_form(int $id, string $action, string $back,
                     string $icon, string $label, string $css,
                     bool $confirm = false): void
{
    // Build the optional "are you sure?" guard for destructive actions.
    $onsubmit = $confirm ? ' onsubmit="return confirm(\'Delete this task?\')"' : '';
    ?>
    <form method="post" action="/action.php"<?= $onsubmit ?>>
        <input type="hidden" name="id"     value="<?= $id ?>">
        <input type="hidden" name="action" value="<?= e($action) ?>">
        <input type="hidden" name="back"   value="<?= e($back) ?>">
        <button class="btn-icon <?= e($css) ?>" title="<?= e($label) ?>">
            <i data-lucide="<?= e($icon) ?>"></i>
        </button>
    </form>
    <?php
}


// get_random_quote() — returns a random ['text' => ..., 'author' => ...] for the
// dashboard banner. New pick on every page load (array_rand).
function get_random_quote(): array
{
    $quotes = [
        ['text' => 'Discipline equals freedom.',                         'author' => 'Jocko Willink'],
        ['text' => 'The secret of getting ahead is getting started.',    'author' => 'Mark Twain'],
        ['text' => 'Done is better than perfect.',                       'author' => 'Sheryl Sandberg'],
        ['text' => 'A year from now you may wish you had started today.', 'author' => 'Karen Lamb'],
        ['text' => 'Little by little, a little becomes a lot.',           'author' => 'Tanzanian proverb'],
        ['text' => "You don't have to be great to start, but you have to start to be great.",
                                                                          'author' => 'Zig Ziglar'],
        ['text' => 'Focus on being productive instead of busy.',         'author' => 'Tim Ferriss'],
        ['text' => "It always seems impossible until it's done.",        'author' => 'Nelson Mandela'],
    ];

    return $quotes[array_rand($quotes)];
}
