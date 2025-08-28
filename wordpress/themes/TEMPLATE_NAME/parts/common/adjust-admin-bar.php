  <?php if (is_user_logged_in()) : ?>
    <style type="text/css">
      body {
        min-height: calc(100vh - 32px) !important;
        min-height: calc(100dvh - 32px) !important;
      }

      .l-header,
      .l-drawer {
        top: 32px !important;
      }

      @media screen and (max-width: 1024px) {
        body {
          min-height: calc(100vh - 32px) !important;
          min-height: calc(100dvh - 32px) !important;
        }

        .l-header,
        .l-drawer {
          top: 32px !important;
        }
      }

      @media screen and (max-width: 782px) {
        body {
          min-height: calc(100vh - 46px) !important;
          min-height: calc(100dvh - 46px) !important;
        }

        .l-header,
        .l-drawer {
          top: 46px !important;
        }
      }

      @media screen and (max-width: 600px) {
        #wpadminbar {
          position: fixed !important;
        }
      }
    </style>
  <?php endif; ?>