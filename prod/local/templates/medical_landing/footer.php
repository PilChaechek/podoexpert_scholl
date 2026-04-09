<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
</main>
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__inner flex flex-wrap items-center gap-y-2 py-4">
            <?php $logoClass = 'site-footer__logo'; include __DIR__ . '/include/logo.php'; ?>
            <nav class="site-footer__menu ml-auto flex flex-wrap gap-x-4 gap-y-1 justify-end text-sm">
                <?php
                CModule::IncludeModule('iblock');
                $docsRes = CIBlockElement::GetList(
                    ['SORT' => 'ASC'],
                    ['IBLOCK_ID' => 3, 'ACTIVE' => 'Y'],
                    false,
                    false,
                    ['NAME', 'CODE']
                );
                while ($doc = $docsRes->Fetch()):
                ?>
                    <a href="/docs/<?= htmlspecialchars($doc['CODE']) ?>/" class="link link--color"><?= htmlspecialchars($doc['NAME']) ?></a>
                <?php endwhile; ?>
            </nav>
        </div>

    </div>
</footer>
</div>
</body>
</html>