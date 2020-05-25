<?php

namespace Backend\Modules\Compression\Actions;

use Backend\Core\Engine\Base\Action;
use Backend\Modules\Compression\Clients\TinyClient;
use Backend\Modules\Compression\Domain\CompressionHistory\CompressionHistoryRepository;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSetting;
use Backend\Modules\Compression\Domain\CompressionSetting\CompressionSettingRepository;
use InvalidArgumentException;
use SplStack;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * In this class, we create a stream of SSE (Server-Sent Events) while compressing images to send realtime feedback to
 * the frontend app (Console tab).
 * @package Backend\Modules\Compression\Actions
 */
class CompressImages extends Action
{
    private const EVENT_NAME = "compression-event";
    private const EVENT_STREAM_END_DELIMITER = "END-OF-STREAM";

    public function execute(): void
    {
        // Stops PHP from checking for user disconnect
        ignore_user_abort(true);

        // How long PHP script stays running/SSE connection stays open (seconds)
        set_time_limit(0);

        // Avoid session locks
        session_write_close();

        // Headers for streaming, must be processed line by line.
        $statusCode = 200;
        header('Connection: keep-alive', false, $statusCode);
        header('Content-Type: text/event-stream; charset=UTF-8', true, $statusCode);
        header('Cache-Control: no-cache', false, $statusCode);
        header('X-Accel-Buffering: no', false, $statusCode); // Disables FastCGI Buffering on Nginx.
        header('HTTP/1.1 200 OK', true, $statusCode);

        // Create a client and stack of images to process
        $client = TinyClient::createFromModuleSettings($this->get('fork.settings'));
        $imagesStack = $this->getImagesFromFolders();

        // Validate that there are images to process
        if ($imagesStack->isEmpty()) {
            $this->sendCompressionEvent("No images to compress found in the selected folders...");
            $this->sendCompressionEvent(self::EVENT_STREAM_END_DELIMITER);
            exit(); // Make sure we don't do fork cms logic after this
        }

        while (!$imagesStack->isEmpty()) {
            if (connection_aborted() === 1) {
                echo "id: Disconnected connection";
                ob_flush();
                flush();
                return;
            }

            // Take one image from stack
            /** @var SplFileInfo $image */
            $image = $imagesStack->pop();

            // Compress image
            // @todo lookup size of image
            $this->sendCompressionEvent("Starting compression of " . $image->getFilename());
            $client->shrinkImage($image);

            // Write to history

            // Send event message
            $this->sendCompressionEvent("Finished compression of " . $image->getFilename());

        }

        if ($imagesStack->isEmpty()) {
            $this->sendCompressionEvent(self::EVENT_STREAM_END_DELIMITER);
            exit(); // Make sure we don't do fork cms logic after this
        }
    }

    private function sendCompressionEvent(string $data): void {
        echo sprintf(
            "id: %s\nevent: %s\ndata: %s\n\n",
            uniqid('', true),
            self::EVENT_NAME,
            $data
        );
        ob_flush();
        flush();
    }

    /**
     * Create a list of every image in the folders we can process, and add them to a stack to make it easier for processing.
     * @return SplStack
     */
    private function getImagesFromFolders(): SplStack
    {
        $images = [];
        $finder = new Finder();

        $settingsRepository = $this->getSettingsRepository();
        $settings = $settingsRepository->findAll();

        /** @var CompressionSetting $setting */
        foreach ($settings as $setting) {
            try {
                $iterator = $finder
                    ->files()
                    ->name('/\.(jpg|jpeg|png)$/i')
                    ->depth('== 0')
                    ->in($setting->getPath());

                /** @var SplFileInfo $imageFile */
                foreach ($iterator as $imageFile) {
                    // Find the image to see if it was processed before
                    $compressionHistoryRecord = $this->getHistoryRepository()
                        ->findBy([
                            'path' => $imageFile->getRealPath(),
                            'checksum' => sha1_file($imageFile->getRealPath())
                        ]);
                    if (!empty($compressionHistoryRecord)) {
                        continue;
                    }

                    $images[] = $imageFile;
                }
            } catch (InvalidArgumentException $e) {
                $this->sendCompressionEvent("Error: cannot process folder: " . $e->getMessage());
            }
        }

        // Create a stack of images so we can easily take an image, process it and take the next one
        return array_reduce(array_reverse($images), static function (SplStack $stack, $path) {
            $stack->push($path);
            return $stack;
        }, new SplStack());
    }

    private function getSettingsRepository(): CompressionSettingRepository
    {
        return $this->get('compression.repository.compression_setting');
    }

    private function getHistoryRepository(): CompressionHistoryRepository
    {
        return $this->get('compression.repository.compression_history');
    }
}
