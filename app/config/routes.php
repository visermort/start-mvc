<?php

return [
    ['GET', '/', 'site.index'],
    ['GET', '/account/logout', 'account.logout'],
    ['GET', '/task/result', 'task.result'],

    [['GET', 'POST'], '/login', 'site.login'],
    [['GET', 'POST'], '/task/create', 'task.create'],
    [['GET', 'POST'], '/task/update/{id:\d+}', 'task.update.admin'], //.admin  - need autorization as admin
];