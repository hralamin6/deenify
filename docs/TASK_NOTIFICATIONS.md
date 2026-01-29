# Task Notification System - Usage Guide

This guide explains how to send notifications with links to specific tasks or any other resource in your application.

## Overview

The notification system has been enhanced to support clickable notifications that navigate users to specific pages when clicked. This is particularly useful for task-related notifications.

## Features

1. **Clickable Notifications**: The entire notification card becomes clickable when a URL is provided
2. **Visual Feedback**: Hover effects and visual indicators show that notifications are interactive
3. **Flexible**: Works with any type of notification (tasks, messages, activities, etc.)
4. **Accessible**: Proper ARIA labels and semantic HTML

## How to Send Task Notifications

### Basic Example

```php
use App\Notifications\TaskNotification;

// Send a notification about a task
$user->notify(new TaskNotification(
    title: 'Task Assigned',
    message: 'You have been assigned a new task: "Complete project documentation"',
    url: route('app.tasks.show', ['task' => $taskId]),
    taskId: $taskId,
    icon: 'o-clipboard-document-check',
    type: 'info'
));
```

### Success Notification

```php
$user->notify(new TaskNotification(
    title: 'Task Completed',
    message: 'Great job! You completed "Design homepage"',
    url: route('app.tasks.show', ['task' => $taskId]),
    taskId: $taskId,
    icon: 'o-check-circle',
    type: 'success'
));
```

### Warning Notification

```php
$user->notify(new TaskNotification(
    title: 'Task Due Soon',
    message: 'Task "Submit report" is due in 2 hours',
    url: route('app.tasks.show', ['task' => $taskId]),
    taskId: $taskId,
    icon: 'o-exclamation-triangle',
    type: 'warning'
));
```

### Error Notification

```php
$user->notify(new TaskNotification(
    title: 'Task Overdue',
    message: 'Task "Review code" is now overdue',
    url: route('app.tasks.show', ['task' => $taskId]),
    taskId: $taskId,
    icon: 'o-x-circle',
    type: 'error'
));
```

## Using with Existing Notifications

You can add URL support to any existing notification by including a `url` field in the `toArray()` method:

```php
public function toArray($notifiable): array
{
    return [
        'title' => 'Your Title',
        'message' => 'Your message',
        'url' => route('app.your-route', ['id' => $this->resourceId]), // Add this
        'icon' => 'o-bell',
        'type' => 'info',
    ];
}
```

## Notification Data Structure

The notification system expects the following data structure in the `data` field:

```php
[
    'title' => 'Notification Title',      // Required
    'message' => 'Notification message',  // Required
    'url' => 'https://...',               // Optional - makes notification clickable
    'icon' => 'o-bell',                   // Optional - defaults to 'o-bell'
    'type' => 'info',                     // Optional - success, error, warning, info
    'task_id' => 123,                     // Optional - for task-specific data
]
```

## Available Icon Types

Common icons you can use:
- `o-check-circle` - Success/completion
- `o-x-circle` - Error/failure
- `o-exclamation-triangle` - Warning
- `o-information-circle` - Info
- `o-clipboard-document-check` - Task/assignment
- `o-bell` - General notification
- `o-chat-bubble-left-right` - Messages
- `o-user-group` - Team/collaboration

## Notification Types

The `type` parameter controls the color scheme:
- `success` - Green theme
- `error` - Red theme
- `warning` - Yellow/orange theme
- `info` - Blue theme (default)

## Example: Task Assignment Flow

```php
// In your TaskController or Service

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskNotification;

class TaskService
{
    public function assignTask(Task $task, User $assignee): void
    {
        // Assign the task
        $task->assignee_id = $assignee->id;
        $task->save();
        
        // Send notification to assignee
        $assignee->notify(new TaskNotification(
            title: 'New Task Assigned',
            message: "You have been assigned: {$task->title}",
            url: route('app.tasks.show', ['task' => $task->id]),
            taskId: $task->id,
            icon: 'o-clipboard-document-check',
            type: 'info'
        ));
        
        // Optionally notify the task creator
        if ($task->creator) {
            $task->creator->notify(new TaskNotification(
                title: 'Task Assigned',
                message: "Task '{$task->title}' has been assigned to {$assignee->name}",
                url: route('app.tasks.show', ['task' => $task->id]),
                taskId: $task->id,
                icon: 'o-check-circle',
                type: 'success'
            ));
        }
    }
    
    public function completeTask(Task $task): void
    {
        $task->status = 'completed';
        $task->completed_at = now();
        $task->save();
        
        // Notify task creator
        if ($task->creator) {
            $task->creator->notify(new TaskNotification(
                title: 'Task Completed',
                message: "{$task->assignee->name} completed: {$task->title}",
                url: route('app.tasks.show', ['task' => $task->id]),
                taskId: $task->id,
                icon: 'o-check-circle',
                type: 'success'
            ));
        }
    }
}
```

## Testing

You can test notifications using the TestNotifications command:

```bash
php artisan test:notifications
```

Or create a test route:

```php
Route::get('/test-task-notification', function () {
    auth()->user()->notify(new \App\Notifications\TaskNotification(
        title: 'Test Task Notification',
        message: 'This is a test notification with a link to dashboard',
        url: route('app.dashboard'),
        icon: 'o-clipboard-document-check',
        type: 'info'
    ));
    
    return redirect()->route('app.notifications')->with('success', 'Test notification sent!');
});
```

## Best Practices

1. **Always provide a URL** for task-related notifications so users can quickly navigate to the task
2. **Use descriptive titles** that clearly indicate what the notification is about
3. **Keep messages concise** but informative
4. **Choose appropriate icons** that match the notification context
5. **Use the correct type** (success, error, warning, info) to set user expectations
6. **Include relevant IDs** (like task_id) in the data for tracking and debugging

## UI Behavior

When a notification has a URL:
- The entire card becomes clickable
- Hover shows a subtle background change
- The title color changes to primary on hover
- A "Click to view" indicator appears on hover
- The action buttons (mark as read, delete) remain clickable and don't trigger navigation
