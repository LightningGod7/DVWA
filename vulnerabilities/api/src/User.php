<?php declare(strict_types=1);

namespace Src;

use OpenApi\Attributes as OAT;

/*
#[OA\Schema()]
enum UserLevel {
    case ADMIN;
    case USER;
}
*/
#[OAT\Schema()]
final class User
{
    #[OAT\Property(type: 'integer', example: 1)]
    public int $id;

    #[OAT\Property(type: "string", example: "fred")]
    public string $name;

    #[OAT\Property(type: 'integer', example: 1)]
    public int $level;

	// Never exposed through the API. Kept private so it cannot be serialised
	// by accident (json_encode / var_export only see public properties).
	private string $password;

	function __construct ($id, $name, $level, $password) {
		if (is_null ($id)) {
			$id = mt_rand(50,100);
		}
		$this->id = $id;
		$this->name = $name;
		$this->level = $level;
		$this->password = $password;
	}

	public function toArray($version) {
		switch ($version) {
			case 1:
			default:
			case 2:
				$a = array (
					"id" => $this->id,
					"name" => $this->name,
					"level" => $this->level,
				);
				break;
		}

		return $a;
	}
}

#[OAT\Schema(required: ['level', 'name'])]
final class UserAdd
{
    #[OAT\Property(example: "fred")]
    public string $name;

    #[OAT\Property(type: 'integer', example: 1)]
    public string $level;
}

#[OAT\Schema(required: ['name'])]
final class UserUpdate
{
    #[OAT\Property(example: "fred")]
    public string $name;
}
