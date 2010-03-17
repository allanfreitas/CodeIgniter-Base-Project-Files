<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title><?= $page_title ?></title>
    <?= $this->ocular->stylesheets(); ?>
    <!--[if IE]>
    <?= $this->ocular->stylesheets('ie'); ?>
    <![endif]-->
  </head>
  <body>
    <?= $this->ocular->message(); ?>
    
    <?= $this->ocular->yield(); ?>
    <?= $this->ocular->javascripts(); ?>
  </body>
</html>