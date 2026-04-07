<?php
$dir = 'app/Filament/Resources';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    $newContent = $content;
    $newContent = str_replace('use Filament\Forms\Form;', 'use Filament\Schemas\Schema;', $newContent);
    $newContent = preg_replace('/public static function form\(Form \$form\): Form/', 'public static function form(Schema $schema): Schema', $newContent);
    $newContent = str_replace('return $form->schema([', 'return $schema->components([', $newContent);
    if ($newContent !== $content) {
        file_put_contents($file->getPathname(), $newContent);
        echo 'Updated: ' . $file->getFilename() . PHP_EOL;
        $count++;
    }
}
echo "Total updated: $count" . PHP_EOL;
