osqa-q2a
========

Import OSQA XML Database Into Q2A

`import-osqa.php` will import OSQA XML Database exports (Users.xml and Posts.xml) and output SQL that can be executed to import all of the questions, answers, users and comments into Question2Answer.

Credit to OSQA (http://www.osqa.net/) and question2answer (http://www.question2answer.org) for two great open source projects.

The code is a little experimental at this stage, but I've tested it on my production Question2Answer site (http://merspi.com.au) and it worked. Please use at your own risk though (and you may want to make the necessary backups before executing it).

It's important to remember to run the config refreshs in the Q2A admin panel once you're done inserting all the SQL data.
