<?php

namespace todolist\system\todo\command;

use todolist\data\todo\Todo;
use todolist\data\todo\TodoEditor;
use wcf\system\exception\SystemException;

final class MarkAsDone
{
    public function __construct(
        private readonly Todo $todo,
        private readonly bool $markAsDone
    ) {
    }

    /**
     * @throws SystemException
     */
    public function __invoke(): void
    {
        $editor = new TodoEditor($this->todo);
        $editor->update(
            [
                'isDone' => $this->markAsDone ? 1 : 0,
            ]
        );
    }
}
