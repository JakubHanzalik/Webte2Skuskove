<?php declare(strict_types=1);

namespace Stuba\Models\Codelist;

use OpenApi\Attributes as OA;
use JsonSerializable;

#[OA\Schema(title: 'CodelistResponseModel', schema: 'CodelistResponseModel', type: 'object')]
class CodelistResponseModel implements JsonSerializable
{
    #[OA\Property(title: "value", type: 'string', example: '1')]
    public string $value;

    #[OA\Property(title: "text", type: 'string', example: "Matematika")]
    public string $text;

    public function __construct()
    {

    }

    public static function constructFromModel($codelistItem): CodelistResponseModel
    {
        $obj = new CodelistResponseModel();

        $obj->value = $codelistItem['value'];
        $obj->text = $codelistItem['text'];

        return $obj;
    }

    function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
