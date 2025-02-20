<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('test.channel', function () {
    return "Hello";
});

############################################## COMMENT ##############################################

Broadcast::channel('Create.Comment.Event', function () {
    return true;
});

Broadcast::channel('Delete.Comment.Event', function ($user, $id) {
    return true;
});


############################################## TICKET ##############################################

Broadcast::channel('Create.Ticket.Event', function () {
    return true;
});

Broadcast::channel('Delete.Ticket.Event', function ($user, $id) {
    return true;
});

Broadcast::channel('Update.Ticket.Event', function ($user, $id) {
    return true;
});

Broadcast::channel('Update.Status.Ticket.Event', function ($user, $id) {
    return true;
});
