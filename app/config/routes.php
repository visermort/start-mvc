<?php

return [
    ['GET', '/', 'task.index'],
    ['GET', '/account', 'account.index'],
    ['GET', '/account/logout', 'account.logout'],
    ['GET', '/task/result', 'task.result'],
    ['GET', '/error503', 'site.notaccess'],

    [['GET', 'POST'], '/login', 'account.login'],
    [['GET', 'POST'], '/task/create', 'task.create'],
    [['GET', 'POST'], '/task/update/{id:\d+}', 'task.update.admin'], //.admin  - need permission admin

   // ['GET', '/createadmin', 'account.createadmin'],

];