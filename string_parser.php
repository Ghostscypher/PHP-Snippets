<?php

require_once 'includes.php';

$string = "
NOTE: HTML supported
Placeholders:
{!ranked_list} - An array of ranked workers that is in the format
    ranked_list[0] = {
        'rank': The rank of the user in the loop e.g. 1, 2, 3 ... etc
        'user': Worker object you can access the contents
        'company': Company user object
        'user.status': Status of the user currently
        'user.score': The user score
    }
<br>
{!user.profile.*} - The worker profile field value
<br>
{!user.*} - Worker value
<br>
{!company.profile.*} - The company user profile field value
<br>
{!company.*} - Company user value
<br>

Note that links also accept parameters
E.g. <a href=\"https://calendly.com/worker/{!user.id}\">Calendly Link</a> will be replaced with <a href=\"https://calendly.com/worker/1\">Calendly Link</a>

If we are using a loop then specify the text inside
{!for:ranked_list}
    Hello {!rank}
    your name is {!user.firstname}
    and your score is {!user.score}%
{/for}

Assuming we have two ranked users, the above will be replaced with something like 

Hello 1
your name is name1
and your score is 50%
<br>
Hello 2
your name is othername
and your score is 20%
";


$example_user = new stdClass();
$example_user->id = 1;
$example_user->first_name = 'Hello';
$example_user->last_name = 'World';
$example_user->inner_value = new stdClass;
$example_user->inner_value->temp = "I am Groot object";
$example_user->inner_value2[0] = "I am Groot array";

$example_user2 = new stdClass();
$example_user2->id = 2;
$example_user2->first_name = 'Hellos';
$example_user2->last_name = 'Worldos';

$template = "
Hi my name is {!companyuser.first_name} {!companyuser.last_name} 
and my user id is {!companyuser.id}
test object access: {!companyuser.inner_value.temp}
test array access: {!companyuser.inner_value2.0}

{#ExampleFunction:companyuser,string}
this is a direct replacement: {!direct}
";

$template1 = "
this is an example of a for loop

{!for:}
{!for:}
    justin
{/for}
{/for}

";

dump(
    $template, 
    "---------------------------------",
    EmailTemplateStringParser::parseTemplate($template, [
        'companyuser' => $example_user,
        'direct' => 91128,
    ])
);
