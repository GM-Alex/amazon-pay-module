#Users demodata
REPLACE INTO `oxuser` SET
    OXID = 'amazonpayuser',
    OXACTIVE = 1,
    OXRIGHTS = 'user',
    OXSHOPID = 1,
    OXUSERNAME = 'amazonpayuser@oxid-esales.dev',
    OXPASSWORD = '$2y$10$tJd1YkFr2y4kUmojqa6NPuHrcMzZmxc9mh4OWQcLONfHg4WXzbtlu',
    OXPASSSALT = '',
    OXFNAME = 'TestUserName',
    OXLNAME = 'TestUserSurname',
    OXSTREET = 'Musterstr.šÄßüл',
    OXSTREETNR = '12',
    OXCITY = 'City',
    OXZIP = '12345',
    OXCOUNTRYID = 'a7c40f631fc920687.20179984',
    OXBIRTHDATE = '1985-02-05 14:42:42',
    OXCREATE = '2021-02-05 14:42:42',
    OXREGISTER = '2021-02-05 14:42:42';

## temp. admin account (username "admin", password "admin")
INSERT INTO `oxuser`
(
    `OXID`,
    `OXACTIVE`,
    `OXRIGHTS`,
    `OXSHOPID`,
    `OXUSERNAME`,
    `OXPASSWORD`,
    `OXPASSSALT`,
    `OXCREATE`,
    `OXREGISTER`,
    `OXBIRTHDATE`
)
VALUES
(
    'tmp_admin',
    1,
    'malladmin',
    1,
    'admin',
    'e3a8a383819630e42d9ef90be2347ea70364b5efbb11dfc59adbf98487e196fffe4ef4b76174a7be3f2338581e507baa61c852b7d52f4378e21bd2de8c1efa5e',
    '61646D696E61646D696E61646D696E',
    NOW(),
    NOW(),
    '1999-12-31'
);
