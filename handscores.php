<?php

class HandScores
{
	/** @var string A 13^5 array of unsigned little-endian 32-bit integers. */
	private $packedArray;

	/**
	 * @param string $serialized A string returned by toSerialized().
	 * @return HandScores An object identical to the HandScores on which toSerialized() was called, at the time
	 *		at which it was called.
	 */
	static public function fromSerialized(string $serialized): HandScores
	{
		$handScores = new HandScores();
		$handScores->packedArray = $serialized;
		return $handScores;
	}

	public function __construct()
	{
		$this->packedArray = str_repeat("\0", 13*13*13*13*13*4);
	}

	/**
	 * @param int[] $cardValues An array of 5 integers, each in [0, 12], that each identify the value of a card.
	 * @param int $score The score of the hand, in the range [0, 0xFFFFFFFF].
	 */
	public function setScore(array $cardValues, int $score): int
	{
		$n = $this->getScoreOffset($cardValues);
		$this->packedArray[$n] = chr($score & 255);
		$this->packedArray[$n + 1] = chr(($score >> 8) & 255);
		$this->packedArray[$n + 2] = chr(($score >> 16) & 255);
		$this->packedArray[$n + 3] = chr($score >> 24);
	}

	/**
	 * @param int[] $cardValues An array of 5 integers, each in [0, 12], that each identify the value of a card.
	 * @return int The score of the hand, in the range [0, 0xFFFFFFFF].
	 */
	public function getScore(array $cardValues): int
	{
		$n = $this->getScoreOffset($cardValues);
		$score = ord($this->packedArray[$n]);
		$score |= ord($this->packedArray[$n + 1]) << 8;
		$score |= ord($this->packedArray[$n + 2]) << 16;
		$score |= ord($this->packedArray[$n + 3]) << 24;
		return $score;
	}

	/**
	 * @return string A string that can be passed to fromSerialized() to construct an identical object later.
	 */
	public function toSerialized(): int
	{
		return $this->packedArray;
	}

	/**
	 * @param int[] $cardValues An array of 5 integers, each in [0, 12], that each identify the value of a card.
	 * @return int The offset into the packed values of the first (least significant) byte of the score.
	 */
	public function getScoreOffset(array $cardValues): int
	{
		$n = 0;
		foreach ($cardValues as $cardValue) {
			$n *= 13;
			$n += $cardValue;
		}
		return $n << 2;
	}
}

$handScores = new HandScores();
$handScores->setScore([2, 4, 10, 8, 5], 0x12345678);
$handScores->setScore([2, 4, 10, 8, 6], 0x24680BDF);

$serialized = $handScores->toSerialized();
$handScores = HandScores::fromSerialized($serialized);

printf("%x %x\n", $handScores->getScore([2, 4, 10, 8, 5]), $handScores->getScore([2, 4, 10, 8, 6]));

// $hs = new HandScores();
// echo $hs->getScoreOffset([0,0,0,0,2]);
// echo PHP_EOL;
// echo $hs->getScoreOffset([13,13,13,13,13]);
