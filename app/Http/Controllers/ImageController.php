<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{

/**
public function compareExternalImage(Request $request)
{
    $externalUrl = $request->query('url');

    if (!$externalUrl) {
        return response()->json(['error' => 'URL da imagem externa é obrigatória.'], 400);
    }

    $localImagePath = storage_path('app/public/uploads/6866e995a9e52.png'); // ou caminho dinâmico

    $pythonBin = '/home/playfunc/virtualenv/webtools/scripts/3.9/bin/python3';
    $scriptPath = base_path('scripts/generate_phash.py');

$env = 'HOME=' . getenv('HOME') ?: '/tmp'; // Ou outro caminho válido para home
$remoteCommand = "$env $pythonBin $scriptPath " . escapeshellarg($externalUrl) . " 2>&1";
exec($remoteCommand, $remoteOutput, $remoteReturn);

$localCommand = "$env $pythonBin $scriptPath " . escapeshellarg($localImagePath) . " 2>&1";
exec($localCommand, $localOutput, $localReturn);


    if ($remoteReturn !== 0 || $localReturn !== 0 || empty($remoteOutput) || empty($localOutput)) {
        return response()->json([
            'error' => 'Erro ao gerar phash.',
            'remote_output' => $remoteOutput,
            'local_output' => $localOutput
        ], 500);
    }

    $hash1 = trim($remoteOutput[0]);
    $hash2 = trim($localOutput[0]);

    $distance = $this->hammingDistance($hash1, $hash2);

    return response()->json([
        'hash_remote' => $hash1,
        'hash_local' => $hash2,
        'distance' => $distance,
        'similar' => $distance <= 10
    ]);
}

private function hammingDistance($hash1, $hash2)
{
    // Validação básica: hashes não podem ser nulos, vazios e devem ter comprimento par
    if (empty($hash1) || empty($hash2)) {
        throw new \InvalidArgumentException('Um dos hashes está vazio.');
    }
    if (strlen($hash1) % 2 !== 0 || strlen($hash2) % 2 !== 0) {
        throw new \InvalidArgumentException('Um dos hashes tem comprimento inválido.');
    }

    $bin1 = @hex2bin($hash1);
    $bin2 = @hex2bin($hash2);

    if ($bin1 === false || $bin2 === false) {
        throw new \InvalidArgumentException('Falha ao converter hash hexadecimal em binário.');
    }

    $dist = 0;
    for ($i = 0; $i < strlen($bin1); $i++) {
        $dist += substr_count(decbin(ord($bin1[$i]) ^ ord($bin2[$i])), '1');
    }
    return $dist;
}

    public function camera()
{
    
    $url = 'https://cards.scryfall.io/large/front/2/f/2f28ecdc-a4f0-4327-a78c-340be41555ee.jpg?1562139726';

$command = escapeshellcmd("/home/playfunc/virtualenv/webtools/scripts/3.9/bin/python3 " .
    base_path('scripts/generate_phash.py') . ' ' . $url);

exec($command, $output, $returnCode);

dd([
    'command' => $command,
    'output' => $output,
    'return_var' => $returnCode,
]);

**/

/**
$pythonPath = '/home/playfunc/virtualenv/webtools/scripts/3.9/bin/python3';
$scriptPath = '/home/playfunc/webtools/scripts/generate_phash.py';
$imagePath = storage_path('app/public/uploads/6866e995a9e52.png');

$command = escapeshellcmd("$pythonPath $scriptPath $imagePath 2>&1");
exec($command, $output, $return_var);

dd([
    'command' => $command,
    'output' => $output,
    'return_var' => $return_var
]);
}**/

    public function camera()
    {
        return view('camera');
    }
    
    public function cameraCheck(Request $request)
    {
        $data = $request->input('image');
    
        if (!$data || !str_starts_with($data, 'data:image')) {
            return response()->json(['message' => 'Imagem inválida.'], 400);
        }
    
        $data = explode(',', $data)[1]; // remove o cabeçalho
        $imageData = base64_decode($data);
    
        $filename = uniqid() . '.png';
        $path = storage_path("app/public/uploads/$filename");
        file_put_contents($path, $imageData);
    
        $scriptPath = base_path('scripts/generate_phash.py');
        $hash = trim(exec("python3 $scriptPath $path"));
    
        $minDistance = PHP_INT_MAX;
        $match = null;

$scriptPath = base_path('scripts/generate_phash.py');
$command = "python3 $scriptPath $path";
$hash = trim(exec($command));

dd([
    'command' => $command,
    'hash' => $hash,
    'path' => $path
]);

        
        dd($hash);
        foreach (Image::all() as $img) {
            $dist = $this->hammingDistance($hash, $img->phash);
            if ($dist < $minDistance) {
                $minDistance = $dist;
                $match = $img;
            }
        }
    
        if ($match && $minDistance <= 10) {
            return response()->json([
                'matched_image' => $match,
                'distance' => $minDistance
            ]);
        }
    
        return response()->json(['message' => 'Nenhuma imagem semelhante encontrada.'], 404);
    }
}


