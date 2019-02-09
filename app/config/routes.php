<?php

return [
    ['GET',           '/', 'task.index'],// controller.action [.permission]
    ['GET',           '/task/result', 'task.result'],
    [['GET', 'POST'], '/login', 'account.login'],

    ['GET',           '/account', 'account.index.auth'],//.auth - need login
    ['GET',           '/account/logout', 'account.logout.auth'],//.auth need login


    [['GET', 'POST'], '/task/create', 'task.create'],
    [['GET', 'POST'], '/task/update/{id:\d+}', 'task.update.admin'], //.admin  - need permission admin

   // ['GET', '/createadmin', 'account.createadmin'], //for start create admin user

];