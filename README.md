# Ctf
An entry-level CTF that introduces core cybersecurity concepts with small, guided challenges. Ideal for students starting their hacking journey.
#!/bin/bash
echo "Importing database schema..."
mysql -u root -p < database_schema.sql

echo "Copying project to /var/www/html/..."
sudo cp -r . /var/www/html/

echo "Restarting Apache..."
sudo systemctl restart apache2

echo "Done! Visit: http://localhost/"
