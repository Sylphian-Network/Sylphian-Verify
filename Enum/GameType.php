<?php

namespace Sylphian\Verify\Enum;

use XF\Phrase;

enum GameType: string
{
	case MINECRAFT = 'minecraft';

	public function label(): Phrase
	{
		return \XF::phrase('sylphian_verify_game_' . $this->value);
	}
}
