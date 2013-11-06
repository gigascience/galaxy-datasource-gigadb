Steps to set up "send to gigagalaxy" function.

Prerequirements:
1. http://localhost will show gigadb webpage. 
2. http://localhost:8080 will show gigagalaxy webpage. 


Steps:
1. Gigadb:
put the view.php file in the proper folder.
In my case, it is here: 
/var/www/hosts/gigadb.cogini.com/htdocs/protected/views/dataset/view.php

2. GigaGalaxy:
2.1 gigadb.xml: Should be put under galaxy-central/tools/data_source/
The gigadb.xml file is in the gigagalaxy folder.


2.2 Add one more line in tool_conf.xml file:
<tool file="data_source/gigadb.xml"/>


Now it should work as expected. 


If you are using a different gigagalaxy URL instead of http://localhost:8080,
there are two places that need to update.
1. In view.php file, update it in line 343 accordingly. 
2. In gigadb.xml file, update it in line 10 accordingly. 
