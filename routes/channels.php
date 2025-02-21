<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('test.channel', function () {
    return "Hello";
});

############################################## COMMENT ##############################################

Broadcast::channel('Create.Comment.Event.{ticketId}', function () {
    return true;
});

Broadcast::channel('Delete.Comment.Event.{ticketId}', function () {
    return true;
});


############################################## TICKET ##############################################

Broadcast::channel('All.Ticket.Event', function () {
    return true;
});

Broadcast::channel('Create.Ticket.Event.{projectId}', function () {
    return true;
});

Broadcast::channel('Delete.Ticket.Event.{projectId}', function ($user, $id) {
    return true;
});

Broadcast::channel('Update.Ticket.Event.{projectId}', function ($user, $id) {
    return true;
});

Broadcast::channel('Update.Status.Ticket.Event.{projectId}', function ($user, $id) {
    return true;
});
