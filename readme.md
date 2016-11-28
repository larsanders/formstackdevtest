## User Management App
*A small but flexible framework for creating, updating and deleting users.*


### Index Page

http://testbox.dev/


### URLs
URLs can be formatted as regular query strings

`http://testbox.dev/index.php?action=validaction&key=value`

or more simply as

`http://testbox.dev/action/key/value/key/value`

or via cli
`php index.php action=verb key=value`


### Output
The app outputs HTML by default. For JSON, add a `format` parameter to the request. 

To show all current users in JSON:
(http://testbox.dev/showall/format/json)
(http://testbox.dev/index.php?action=showall&format=json)
`php index.php action=showall format=json`

To change the default output format app-wide, edit this property in UserModel.php:
`public $format = 'html';`


### Create User
Creating a new user requires all four parameters.
Returns the id number of the new user, or an error message.
*Note that in production, I would not display a PDO Exception to the user.*

| Key | Value |
| ------------- | ------------- |
| email  | 6 to 100 characters, with @ symbol<br> **MUST be unique in the database** |
| first\_name  | 1 - 40 alphanumeric characters |
| last\_name | 1 - 40 alphanumeric characters |
| password | 8 characters, with 1 number and 1 special character <br> **MUST NOT contain ‘#’ or '/' when using HTTP** |

HTTP
(http://testbox.dev/index.php?action=create&email=e@mail.io&first\_name=frank&last\_name=gehry&password=8c88\*SW1)
(http://testbox.dev/create/email/e@mail.io/first\_name/frank/last\_name/gehry/password/8c88*SW1)

CLI 
`php index.php action=create email=e@mail.io first_name=frank last\_name=gehry password=8c88*SW1`


### Update User
Updating a user requires an id number and at least one of four parameters. 
See the Create User section for parameter requirements.
Returns a success or failure string.

(http://testbox.dev/index.php?action=update&id=78&first\_name=nickname&last\_name=gotmarried)
(http://testbox.dev/update/id/78/first\_name/nickname/last\_name/gotmarried)
`php index.php action=update id=78 first\_name=nickname last\_name=gotmarried`


### Delete User
Deleting a user requires only the database id number.
Returns a success or failure string.

(http://testbox.dev/index.php?action=delete&id=78)
(http://testbox.dev/delete/id/78)
`php index.php action=delete id=78`


### Database Schema
In the schema folder. 
There are two tables - `users` for the app and `users_test` for unit testing.


### Code Coverage
In the `tests/coverage` folder or at (http://testbox.dev/tests/coverage/)
For several class methods, the final bracket registers as not executed. 
This is preventing UserView.php and UserModel.php from hitting 100%.
