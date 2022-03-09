<?php
/**
 * @var $folder
 */

function print_folder(TestFolder $folder)
{
    $correct = $folder->is_correct(); ?>
    <li class="folder collapsed <?= $correct ? 'green ' : 'red' ?>">
        <h3>Folder: <?= $folder->get_path() ?>
            <button class="open-close" data-alt="Close">Open</button>
        </h3>
        <p>Success: <?= $folder->get_correct_tests() ?>/ <?= $folder->get_total_tests() ?></p>

        <!-- Subitems (tests, folders) -->
        <ul>
            <!-- Tests -->
            <?php foreach ($folder->get_tests() as $test):
                $correct = $test->is_correct();
                $ret_correct = $test->is_correct_ret_code() ?>
                <li class="folder <?= $correct ? 'green' : 'red' ?>">
                    <h3>Test: <?= $test->get_path() ?> </h3>
                    <p>Error code
                        was <?= $ret_correct ? 'OK' : "WRONG (was {$test->get_ret_code()} should be {$test->get_reference_ret_code()})" ?></p>
                    <?php if ($ret_correct): /* Show result only if error code is ok */ ?>
                        <p>Result was <?= $correct ? 'OK' : "WRONG" ?></p>
                        <?php if (!$correct): ?>
                            <pre>
                            <code>
                                <?= htmlspecialchars($test->get_xml_delta()) ?>
                            </code>
                        </pre>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Result was not tested</p>
                    <?php endif ?>
                </li>
            <?php endforeach; ?>

            <!-- Folders -->
            <?php foreach ($folder->getSubfolders() as $subfolder) {
                print_folder($subfolder);
            } ?>
        </ul>
    </li>
<?php } ?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8"/>
    <title>Test results</title>

    <style>
        .folder {
            padding: 10px;
            margin: 20px 0;
        }

        .folder .folder {
            margin-left: 20px;
        }

        .green.folder > h3 {
            background: #C8E6C9;
            color: #1B5E20;
        }

        .red.folder > h3 {
            background: #FFCDD2;
            color: #B71C1C;
        }

        p {
            margin: 0;
        }

        .folder.collapsed .folder {
            display: none;
        }
    </style>

</head>
<body>
<h1>Test results</h1>

<ul>
    <?php print_folder($folder) ?>
    <!--<li class="folder red">
        <h3>Folder: tests/</h3>
        <p>Success: 3/10</p>

        <ul>
            <li class="folder red">
                <h3>Folder: tests/parse</h3>
                <p>Success: 3/10</p>

                <ul>
                    <li class="folder green">
                        <h3>Test: tests/parse/read_test</h3>
                        <p>Error code was OK</p>
                        <p>Result was WRONG</p>
                    </li>
                    <li class="folder green">
                        <h3>Test: tests/parse/read_test</h3>
                        <p>Error code was OK</p>
                        <p>Result was WRONG</p>
                    </li>
                    <li class="folder red">
                        <h3>Test: tests/parse/read_test</h3>
                        <p>Error code was OK</p>
                        <p>Result was WRONG</p>
                    </li>
                </ul>

            </li>

            <li class="folder red">
                <h3>Folder: tests/both</h3>
                <p>Success: 3/10</p>
            </li>

            <li class="folder red">
                <h3>Test: tests/test</h3>
                <p>Error code was OK</p>
                <p>Result was WRONG</p>
            </li>
        </ul>

    </li>

    -->
</ul>

<?php /** @var $tests TestItem[] */
/*foreach ($folder as $test):
    $correct = $test->is_correct(); ?>
    <div class="test <?= $correct ? 'green' : 'red' ?>">
        <h3><?= $test->get_path() ?></h3>
        <p>Parser return code: <?= $test->get_parser_ret_code() ?></p>
        <p>Parser output: <?= $test-> ?></p>
        <?php if (!$test->is_correct()): ?>
            <div class="wrong-result">
                <pre><code><?= htmlspecialchars($test->get_xml_delta()) ?></code></pre>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; */ ?>

<script>
    document.querySelectorAll('.folder').forEach(folder => {
        var btn = folder.querySelector(".open-close");
        if (btn) {
            btn.addEventListener('click', (event) => {
                event.stopPropagation();
                event.preventDefault();
                folder.classList.toggle('collapsed');

                var tmp = btn.textContent;
                btn.textContent = btn.getAttribute('data-alt');
                btn.setAttribute('data-alt', tmp);
            });
        }
    })
</script>
</body>
</html>