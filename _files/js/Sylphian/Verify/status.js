((window, document) =>
{
    'use strict';

    XF.ServerStatusLoader = XF.Element.newHandler({
        options: {
            serverId: null,
            statusUrl: null
        },

        init()
        {
            if (!this.options.serverId || !this.options.statusUrl)
            {
                return;
            }

            this.loadStatus();
        },

        loadStatus()
        {
            XF.ajax('GET', this.options.statusUrl, {
                server_id: this.options.serverId
            }, (data) => {
                if (data && data.html)
                {
                    XF.setupHtmlInsert(data.html, this.target);
                }
                else
                {
                    console.error('XF server status AJAX failed: No HTML returned.', data);
                }
            }, { skipDefaultSuccessError: true }).catch((error) => {
                console.error('XF server status AJAX error:', error);
            });
        }
    });

    XF.Element.register('server-status-loader', 'XF.ServerStatusLoader');
})(window, document);
