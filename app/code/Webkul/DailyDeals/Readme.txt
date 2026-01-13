#Installation

Magento 2 Daily Deals module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and DailyDeals(module) name folder inside your magento/app/code/ directory and then move all module's files into magento2 root directory /app/code/Webkul/DailyDeals/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy


2. Flush the cache and reindex all.

now module is properly installed

#Uninstallation

Note: After uninstallation, all data of the module will be deleted from the instance. It will completely uninstall the module.

Please follow the steps for uninstallation-

1.  Run Following Command via terminal
    -----------------------------------
    php bin/magento dailydeal:disable
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

#Reinstallation

If this module has been uninstalled by using the upper Uninstallation process then Please follow the steps for reinstallation-

1.  Run Following Command via terminal
    -----------------------------------
    php bin/magento module:enable Webkul_DailyDeals
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.


#User Guide

For Magento 2 Daily Deals module's working process follow user guide : http://webkul.com/blog/magento2-daily-deals/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/