var oneClickUpgradeRemoteEndpoint = "https://connect.duplicator.com/upgrade-free-to-pro";

jQuery(document).ready(function ($) {
    if ($('#dup-settings-upgrade-license-key').length) {
        // Client-side redirect to oneClickUpgradeRemoteEndpoint
        function redirectToRemoteEndpoint(data) {
            $("#redirect-to-remote-upgrade-endpoint").attr("action", oneClickUpgradeRemoteEndpoint);
            $("#form-oth").attr("value", data["oth"]);
            $("#form-key").attr("value", data["license_key"]);
            $("#form-version").attr("value", data["version"]);
            $("#form-redirect").attr("value", data["redirect"]);
            $("#form-endpoint").attr("value", data["endpoint"]);
            $("#form-siteurl").attr("value", data["siteurl"]);
            $("#form-homeurl").attr("value", data["homeurl"]);
            $("#form-file").attr("value", data["file"]);
            $("#redirect-to-remote-upgrade-endpoint").submit();
        }
        
        $('#dup-settings-connect-btn').on('click', function (event) {
            event.stopPropagation();
            var license_key = $('#dup-settings-upgrade-license-key').eq(0).val();
            Duplicator.Util.ajaxWrapper(
                {
                    action: 'duplicator_one_click_upgrade_prepare',
                    nonce: dup_one_click_upgrade_script_data.nonce_one_click_upgrade,
                    license_key: license_key
                },
                function (result, data, funcData, textStatus, jqXHR) {
                    redirectToRemoteEndpoint(funcData);                        
                },
                function (result, data, funcData, textStatus, jqXHR) {
                    let errorMsg = `<p>
                        <b>${dup_one_click_upgrade_script_data.fail_notice_title}</b>
                    </p>
                    <p>
                        ${dup_one_click_upgrade_script_data.fail_notice_message_label} ${data.message}<br>
                        ${dup_one_click_upgrade_script_data.fail_notice_suggestion}
                    </p>`;
                    Duplicator.addAdminMessage(errorMsg, 'error');
                }
            );
        });
    }
});
