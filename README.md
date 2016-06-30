# stockDB

Usage:

http://localhost/stockDB/index.php - FrontEnd

Description:

1. Download XAMPP and install on your machine which XAMPP supports.

2. Config apache, find the root directory of your web server.

3. Config mysql, set root password and carry to config file of phpMyAdmin.

4. Execute 'git clone https://github.com/ysyang21/stockDB.git' on root
   directory of apache web server, which makes webpages work.

5. Edit LIB_mysql.php, use the password to decrypt util.zip, then modify to
   your own according to step 3.

6. Check restore_mem.sh and restore_inn.sh in decrypted util folder, one is
   for seeding MEMORY engine database stockDB and the other for InnoDB engine
   DB, I prefer MEMORY version myself.

Terms and Conditions:

    What this system renders are for your information and interests only. They
    are not intended to amount to investment advice nor should they be relied
    on in making your investment or other decision.

[2016/06/30] v0.02 --> verdict feature added
[2016/05/24] v0.01 --> first full release
[2016/04/13] start using github for this project