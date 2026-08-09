<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$announcements = [
    [
        'date' => '2025-07-09',
        'title' => '2024 sheets',
        'body' => <<<'MARKDOWN'
2024 character sheets are here! When creating a new sheet, you can choose the 2014 version or the 2024 version. Existing character sheets will remain in the 2014 style.

The app is now much more usable on mobile.
MARKDOWN
    ],
    [
        'date' => '2025-12-19',
        'title' => 'Trackable fields',
        'body' => <<<'MARKDOWN'
There is a new trackable fields section for keeping up with rechargeable class features like focus points and superiority dice, as well as things like attuned magic items.
MARKDOWN
    ],
    [
        'date' => '2025-12-23',
        'title' => 'New design',
        'body' => <<<'MARKDOWN'
I restyled the UI from scratch. The general look and feel is the same but there are many subtle improvements. In particular you should notice an improved mobile experience. You may need to hard refresh your browser if you aren’t seeing the new design.

This update will make it easier for me to add new features in the future so stay tuned! As always, you can [shoot me an email](mailto:blake@blakewatson.com) if you have any problems, feedback, or requests.
MARKDOWN
    ],
    [
        'date' => '2025-12-26',
        'title' => 'Open5e Content search',
        'body' => <<<'MARKDOWN'
You will find a new spell book button in the tab bar. Clicking that will launch a window for searching [Open5e](https://open5e.com/) for spells or backgrounds. Search for the content you want and click the copy button. You can then paste it into your character sheet. I will be adding support for other content types on an ongoing basis. If there is a particular type of content you would like to see supported, [let me know](mailto:blake@blakewatson.com).

Notes have moved into the details tab to make room for the new button.
MARKDOWN
    ],
    [
        'date' => '2026-03-20',
        'title' => 'German beta',
        'body' => <<<'MARKDOWN'
Experimental beta support for German language is now available! You can switch your language using the Language setting above. Please note that for now, the translation applies to the character sheet only and does not extend to the dashboard. If you notice any errors or mistranslations, please [let me know](mailto:blake@blakewatson.com).
MARKDOWN
    ],
    [
        'date' => '2026-04-01',
        'title' => 'Character sheet management',
        'body' => <<<'MARKDOWN'
You can now import, export, and duplicate character sheets right from the dashboard, making it easy to share sheets between accounts or use an existing sheet as a starting point. I also did some spring cleaning with the code behind the scenes. Please [get in touch](mailto:blake@blakewatson.com) if you come across any problems.
MARKDOWN
    ],
    [
        'date' => '2026-04-13',
        'title' => 'Dice rolling',
        'body' => <<<'MARKDOWN'
You can now roll dice directly from the character sheet! You’ll see a *Dice* panel at the bottom of the page. Click it to open. Select any combination of dice then click *Roll*.

On desktop size, you can maximize the panel by clicking the little diagonal arrows icon. That will give you a fixed side-by-side view of your sheet and your dice.

The dice roller integrates with RANDOM.ORG to give you true randomness based on atmospheric noise.

If you don’t want to see the dice roller at all, there is a new setting to hide it.
MARKDOWN
    ],
    [
        'date' => '2026-05-23',
        'title' => 'Email login fix',
        'body' => <<<'MARKDOWN'
I fixed an issue where email addresses were case-sensitive when registering and logging in. That’s clearly not how they should be and I feel quite silly that it went on this long. I’ve fixed that issue and am treating email addresses as case-insensitive now.

The side effect of this is that if for some reason you had two accounts—for example one where you capitalized your email and one where you didn’t—then only one of those accounts exists now. Only a small number of accounts had these duplicate accounts. If yours happens to be one of them, your character sheets in all the accounts with the same email are now together in your one account. If you ended up needing to reset your password this is probably why.

No one else should notice much of anything as this was all behind-the-scenes. This was a critical thing to fix but now that it’s done I can get back to adding features! If you notice anything wonky, as always, please don’t hesitate to contact me.
MARKDOWN
    ],
    [
        'date' => '2026-07-13',
        'title' => 'Dropping inline image support',
        'body' => <<<'MARKDOWN'
The app has supported pasting images into its text boxes for some time now, but that support was not intentional. It was a happy accident that I never got around to fixing. However, it has caused some issues with the way the app handles character sheet data and I will be removing that functionality on August 16, 2026. This won’t affect most users but if you were using inline images, I apologize for removing the feature.

I may be able to support images in the future, and if I do that they will be handled in a much smarter manner. If having images in your character sheet is something you are interested in, [email me](mailto:blake@blakewatson.com) and let me know!

But in the meantime, for the stability of the app, I need to remove support for pasting images.
MARKDOWN
    ],
    [
        'date' => '2026-07-21',
        'title' => 'Class feature search',
        'body' => <<<'MARKDOWN'
In the content search panel (the spellbook icon) you can now search for class features from multiple editions of D&D. The search results are structured as an à la carte menu of information about your chosen class. Click the checkboxes for any of the features you’d like to copy, then click one button to copy everything at once. Paste beautifully formatted content directly into your character sheet.

This level of integration was quite an undertaking and there may be bugs. I’m calling this feature experimental for now. Please let me know if you run into any issues or have any feedback. Please note that I don’t control the content that comes from the Open5e API. I can’t necessarily do anything about missing or inaccurate content.

The Open5e API can only serve content that is under an open license. It releases some official D&D content under the [System Reference Document](https://www.dndbeyond.com/srd), which includes a lot—but not all—of the content covered in the Player’s Handbook. In particular, each class only includes one subclass. That means if your subclass isn’t covered in the System Reference Document, it won’t be included in the search results from Open5e. You will have to copy that content from official sources to paste it into your character sheet.

I still have more integrations planned for the content search panel so let me know if there is an aspect of it you’d like me to work on first.
MARKDOWN
    ]
];

try {
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->exec('BEGIN;');

    $post_columns = array_column($db->exec('PRAGMA table_info("post");'), 'name');
    $required_columns = ['title', 'body', 'published_at', 'created_at', 'updated_at'];

    if (array_diff($required_columns, $post_columns)) {
        throw new RuntimeException('Migration 008 must be run before importing announcements.');
    }

    foreach ($announcements as $announcement) {
        $published_at = $announcement['date'] . ' 00:00:00';

        $existing_post = $db->exec(
            'SELECT id FROM "post" WHERE body = ? AND published_at = ? LIMIT 1;',
            [$announcement['body'], $published_at]
        );

        if ($existing_post) {
            continue;
        }

        $db->exec(
            'INSERT INTO "post"
                (title, body, is_maintenance_message, published_at, created_at, updated_at)
             VALUES (?, ?, 0, ?, ?, ?);',
            [
                $announcement['title'],
                $announcement['body'],
                $published_at,
                $published_at,
                $published_at
            ]
        );
    }

    $db->exec('COMMIT;');
} catch (Throwable $e) {
    try {
        $db->exec('ROLLBACK;');
    } catch (Throwable $rollback_exception) {
        // Ignore rollback failures so the original migration error is reported.
    }

    fwrite(STDERR, 'Announcement migration failed: ' . $e->getMessage() . "\n");
    exit(1);
}

die;
