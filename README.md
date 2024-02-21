## About User Rest Api
You can store users in database via User Rest Api.
- **POST - api/user/**
example:
  $client = new Client();
  $options = [
    'multipart' => [
      [
        'name' => 'firstname',
        'contents' => 'John'
      ],
      [
        'name' => 'lastname',
        'contents' => 'Doe'
      ],
      [
        'name' => 'password',
        'contents' => '12345678'
      ],
      [
        'name' => 'email',
        'contents' => 'johndoe@gmail.com'
      ]
  ]];
  $request = new Request('POST', 'http://127.0.0.1:8741/api/user');
  $res = $client->sendAsync($request, $options)->wait();
  echo $res->getBody();

  
**You can get a list of all users:**
- GET - api/user/

**You can get a user by ID:**
- GET - api/user/{id}

### Tech stack:
- php
- symfony
- docker

### Start:
- **clone**
- **cd user-rest-api**
- **docker compose up --build**
- **enter api_backend container and run: php bin/console doctrine:database:create**
- **run: php bin/console doctrine:migrations:migrate**
