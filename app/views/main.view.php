<?php
    require_once 'partials/header.php'; 
    use App\Core\Request;
    use App\Controllers\PriceSheetController;

    $pc = new PriceSheetController();
    $pc->cgiUploadSheet();
  ?>

    <div id="root"></div>
    <!--
      This HTML file is a template.
      If you open it directly in the browser, you will see an empty page.

      You can add webfonts, meta tags, or analytics to this file.
      The build step will place the bundled scripts into the <body> tag.

      To begin the development, run `npm start` or `yarn start`.
      To create a production bundle, use `npm run build` or `yarn build`.
    -->
    <?php
    require_once 'partials/footer.php'; 