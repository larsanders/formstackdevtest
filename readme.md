## User Management App
*A small but flexible framework for creating, updating and deleting users.*


### Index Page

http://testbox.dev/


### URLs
Requests can be formatted in 3 ways.

HTTP<br>
`//testbox.dev/verb/key/value/key/value`
`//testbox.dev/index.php?action=verb&key=value`

CLI <br>
`php index.php action=verb key=value`

VERBS<br>
`create update delete showall`

### Output
The app outputs HTML by default. For JSON, add a `format` parameter to the request. 

To show all current users in JSON:<br>
[http://testbox.dev/showall/format/json](http://testbox.dev/showall/format/json)<br>
[http://testbox.dev/index.php?action=showall&format=json](http://testbox.dev/index.php?action=showall&format=json)<br>
`php index.php action=showall format=json`


### Create User
Creating a new user requires all four parameters.<br>
Returns the id number of the new user, or an error message.<br>
*Note that this app exposes exceptions. In production, I would not display exceptions to the user.*

| Key | Value |
| ------------- | ------------- |
| email  | 6 - 100 characters, with @ symbol<br> **MUST be unique in the database** |
| first\_name  | 1 - 40 alphanumeric characters |
| last\_name | 1 - 40 alphanumeric characters |
| password | 8 characters, with 1 number and 1 special character <br> **MUST NOT contain ‘#’ or '/' when using HTTP** |

[index.php?action=create&email=e@mail.io&first_name=frank&last_name=gehry&password=8c88*SW1](http://testbox.dev/index.php?action=create&email=e@mail.io&first_name=frank&last_name=gehry&password=8c88\*SW1)<br>
[create/email/e@mail.io/first_name/frank/last_name/gehry/password/8c88*SW1](http://testbox.dev/create/email/e@mail.io/first_name/frank/last_name/gehry/password/8c88*SW1)<br>
`php index.php action=create email=e@mail.io first_name=frank last_name=gehry password=8c88*SW1`


### Update User
Updating a user requires an id number and at least one of four parameters.<br>
See the Create User section for parameter requirements.<br>
Returns a success or failure string.

[index.php?action=update&id=1&first_name=nickname&last_name=gotmarried](http://testbox.dev/index.php?action=update&id=1&first_name=nickname&last_name=gotmarried)<br>
[update/id/1/first_name/nickname/last_name/gotmarried](http://testbox.dev/update/id/1/first_name/nickname/last_name/gotmarried)<br>
`php index.php action=update id=1 first_name=nickname last_name=gotmarried`


### Delete User
Deleting a user requires only the database id number.
Returns a success or failure string.

[index.php?action=delete&id=1](http://testbox.dev/index.php?action=delete&id=1)<br>
[testbox.dev/delete/id/1](http://testbox.dev/delete/id/1)<br>
`php index.php action=delete id=1`


### Database Schema
In the schema folder. 
There are two tables - `users` for the app and `users_test` for unit testing.


### Code Coverage
http://testbox.dev/tests/coverage/ <br>
For several class methods, the final bracket registers as not executed. <br>
This is preventing UserView.php and UserModel.php from hitting 100%.
