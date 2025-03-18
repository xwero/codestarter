<?= "<?php\n" ?>

namespace <?= $content->namespace; ?>;

<?= $content->getImports() ?>

<?= $content->getTypeDefinition() ?>
{
    <?= "\n".$content->getCases().$content->getMethods()."\n\n" ?>
}

