<?php $pager->setSurroundCount(2) ?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
<div class="row">
    <!-- Pagination -->
    <div class="col-12">
    <ul class="pagination justify-content-center">
        <?php if ($pager->hasPreviousPage()) : ?>
            <li>
                <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
                    <span aria-hidden="false"><?= lang('Pager.first') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getPreviousPage() ?>" aria-label="<?= lang('Pager.previous') ?>">
                    <span aria-hidden="false"><?= lang('Pager.previous') ?></span>
                </a>
            </li>
        <?php endif ?>

        <!-- Page Links -->
        <?php foreach($pager->links() as $link): ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>"><a class="page-link"
                    href="<?= $link['uri'] ?>"><?= $link['title'] ?>|</a></li>
            <?php endforeach; ?>
            <!-- End of Page Links -->

        <?php if ($pager->hasNextPage()) : ?>
            <li>
                <a href="<?= $pager->getNextPage() ?>" aria-label="<?= lang('Pager.next') ?>">
                    <span aria-hidden="false"><?= lang('Pager.next') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
                    <span aria-hidden="false"><?= lang('Pager.last') ?></span>
                </a>
            </li>
        <?php endif ?>
    </ul>
    </div>
</div>
</nav>
