define([], function () {
    'use strict';

    return function init(config) {
        try {
            var head = document.head || document.getElementsByTagName('head')[0];
            var base = String(config.baseUrl || '').replace(/\/+$/, '') + '/';
            var siteName = config.siteName || 'Gila Electric';
            var orgLogo = config.orgLogo || 'https://gilaelectric.com.eg/media/codazon/logo/4/default/Gilalogo.png';
            var breadcrumbName = config.breadcrumbName || 'Home';

            function inject(id, obj) {
                var prev = document.getElementById(id);
                if (prev && prev.parentNode) prev.parentNode.removeChild(prev);
                var s = document.createElement('script');
                s.type = 'application/ld+json';
                s.id = id;
                s.text = JSON.stringify(obj);
                head.appendChild(s);
            }

            var website = {
                '@context': 'https://schema.org',
                '@type': 'WebSite',
                url: base,
                name: siteName,
                potentialAction: {
                    '@type': 'SearchAction',
                    target: base + 'catalogsearch/result/?q={search_term_string}',
                    'query-input': 'required name=search_term_string'
                }
            };

            var organization = {
                '@context': 'https://schema.org',
                '@type': 'Organization',
                url: base,
                name: siteName,
                logo: orgLogo
            };

            var breadcrumb = {
                '@context': 'https://schema.org',
                '@type': 'BreadcrumbList',
                itemListElement: [{
                    '@type': 'ListItem',
                    position: 1,
                    name: breadcrumbName,
                    item: base
                }]
            };

            inject('ignitix-sd-website', website);
            inject('ignitix-sd-organization', organization);
            inject('ignitix-sd-breadcrumb', breadcrumb);
        } catch (e) {
            // no-op
        }
    };
});