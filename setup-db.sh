#!/bin/bash
# Update database credentials in laravel/.env
cd laravel

# Update DB_DATABASE
if grep -q "^DB_DATABASE=" .env; then
  sed -i 's/^DB_DATABASE=.*/DB_DATABASE=cvthnzkenm/' .env
else
  echo "DB_DATABASE=cvthnzkenm" >> .env
fi

# Update DB_USERNAME
if grep -q "^DB_USERNAME=" .env; then
  sed -i 's/^DB_USERNAME=.*/DB_USERNAME=cvthnzkenm/' .env
else
  echo "DB_USERNAME=cvthnzkenm" >> .env
fi

# Update DB_PASSWORD
if grep -q "^DB_PASSWORD=" .env; then
  sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=fChJw9PVnq/' .env
else
  echo "DB_PASSWORD=fChJw9PVnq" >> .env
fi

# Generate App Key if missing
if grep -q "^APP_KEY=$" .env || ! grep -q "^APP_KEY=" .env; then
    php artisan key:generate
fi

echo "Database credentials updated."

