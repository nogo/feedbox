API Documentaion
================

GET /user/{name}

* Show profile
* List of public links

PUT /user/{name}

* Change Firstname, Lastname, Username, Email
* MUST BE authorized

GET /user/{name}/{tag}

* List of items with this tag
* Public Tags are accessable without authentization
* Tagname unread, read and starred are systembased

PUT /user/{name}/{tag}

* Change the tag name
* MUST BE authorized

PUT /user

* Register user
* MUST BE NOT authorized

DELETE /user/{name}

* MUST BE authorized

GET /source

* List of all user sources
* MUST BE authorized

POST /source

* Add new source
* MUST BE authorized

PUT /source/{name}

* Change source details
* MUST BE authorized

DELETE /source/{name}

* Remove source and its items only with tag read
* MUST BE authorized

POST /item

* Add new item, content will be fetched
* MUST BE authorized

PUT /item/{id}

* Update item details
* MUST BE authorized

GET /setting

* Get all user settings
* MUST BE authorized

POST /setting

* Add new setting
* MUST BE authorized

PUT /setting/{name}

* Update setting details
* MUST BE authorized

DELETE /setting/{name}

* Remove setting
* MUST BE authorized
