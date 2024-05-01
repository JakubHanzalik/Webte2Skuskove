<?php declare(strict_types=1);

namespace Stuba\Models\Codelist;

use OpenApi\Attributes as OA;
use JsonSerializable;

#[OA\Schema(title: 'CodelistResponseModel', schema: 'CodelistResponseModel', type: 'object')]
class CodelistResponseModel implements JsonSerializable
{
    public function __construct($user)
    {
        $this->value = $user['value'];
        $this->text = $user['text'];
    }

    function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    #[OA\Property(title: "value", type: 'string', example: '1')]
    public string $value;

    #[OA\Property(title: "text", type: 'string', example: "Matematika")]
    public string $text;
}
