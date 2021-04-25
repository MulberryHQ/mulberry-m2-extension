<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Mulberry\Warranty\Model;

use Exception;
use Mulberry\Warranty\Api\Model\CrawlerInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Http\Exception\RuntimeException;

class Crawler implements CrawlerInterface
{
    /**
     * Maximum page size
     */
    public const PRODUCT_COLLECTION_PAGE_SIZE = 100;

    /**
     * File name
     */
    public const CRAWLER_FILE_NAME = 'crawlerData.json';

    /**
     * File name for paginated content
     */
    public const PAGINATED_CRAWLER_FILE_NAME = 'crawlerDataPaginated.json';

    /**
     * Separator for paginated file
     */
    public const PAGINATED_CRAWLER_FILE_SEPARATOR = '|';

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var string
     */
    private $mediaUrl;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $categoryUrlCache = [];

    /**
     * @var File
     */
    private $file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Crawler constructor.
     * @param CollectionFactory $collectionFactory
     * @param Json $serializer
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepository $categoryRepository
     * @param File $driverFile
     * @param DirectoryList $directoryList
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Json $serializer,
        StoreManagerInterface $storeManager,
        CategoryRepository $categoryRepository,
        File $driverFile,
        DirectoryList $directoryList
    ) {
        $this->serializer = $serializer;
        $this->productCollectionFactory = $collectionFactory;
        $this->mediaUrl = rtrim($storeManager->getDefaultStoreView()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/');
        $this->categoryRepository = $categoryRepository;
        $this->file = $driverFile;
        $this->directoryList = $directoryList;
    }

    /**
     * {@inheritDoc}
     */
    public function exportProducts(): array
    {
        try {
            $productCollection = $this->getProductCollection();
            $result = $this->crawlProductCollection($productCollection);
            $this->writeToFile($this->serializer->serialize($result), self::CRAWLER_FILE_NAME, true);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Error while crawling products: %s', $e->getMessage()));
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function exportProductsByPage($page): array
    {
        $result = [
            'content' => [],
            'lastPage' => '',
        ];
        try {
            $count = 0;
            $productCollection = $this->getProductCollection($page);
            $result['lastPage'] = $productCollection->getLastPageNumber();
            foreach ($productCollection->getItems() as $product) {
                $count++;
                $result['content'][] = $this->fetchProductData($product);
            }

            $json = $this->serializer->serialize($result['content']) . self::PAGINATED_CRAWLER_FILE_SEPARATOR;
            $this->writeToFile(
                $json,
                self::PAGINATED_CRAWLER_FILE_NAME,
                $page == 1
            );
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Error while crawling products: %s', $e->getMessage()));
        }

        return $result;
    }

    /**
     * @param int $page
     * @return Collection
     */
    private function getProductCollection($page = 1): Collection
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->setPageSize(self::PRODUCT_COLLECTION_PAGE_SIZE);
        $productCollection->setCurPage($page);

        return $productCollection;
    }

    /**
     * @param Collection $productCollection
     * @return array
     * @throws LocalizedException
     */
    private function crawlProductCollection(Collection $productCollection): array
    {
        $data = [];
        $page = 1;
        $lastPage = $productCollection->getLastPageNumber();
        while ($page <= $lastPage) {
            // print_r(sprintf('Updating page %s of %s%s', $page, $lastPage, PHP_EOL));
            $productCollection->addMediaGalleryData();
            foreach ($productCollection->getItems() as $product) {
                $data[] = $this->fetchProductData($product);
            }
            $productCollection->setCurPage(++$page)->resetData();
        }

        return $data;
    }

    /**
     * @param Product $product
     * @return array
     * @throws LocalizedException
     */
    private function fetchProductData(Product $product): array
    {
        $result = [];

        $result['title'] = $product->getName();
        $result['text'] = $product->getDescription();
        $result['price'] = $product->getPrice();
        $result['url'] = preg_replace('/\?.*/', '', $product->getUrlInStore());
        $result['images'] = $this->fetchProductImages($product);
        $result['categories'] = $this->fetchProductCategoryData($product);

        return $result;
    }

    /**
     * @param Product $product
     * @return array
     * @throws LocalizedException
     */
    private function fetchProductImages(Product $product): array
    {
        $result = [];
        $productMediaEntities = $product->getMediaGalleryEntries();
        if ($productMediaEntities) {
            foreach ($productMediaEntities as $mediaEntity) {
                $result[] = sprintf("%s%s", $this->mediaUrl, $mediaEntity->getFile());
            }
        }
        return $result;
    }

    /**
     * @param Product $product
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchProductCategoryData(Product $product): array
    {
        $result = [];
        if ($product->getCategoryIds()) {
            foreach ($product->getCategoryIds() as $categoryId) {
                if (!isset($this->categoryUrlCache[$categoryId])) {
                    $category = $this->categoryRepository->get($categoryId);
                    $this->categoryUrlCache[$categoryId]['name'] = $category->getName();
                    $this->categoryUrlCache[$categoryId]['url'] = $category->getUrl();
                }

                $result[] = $this->categoryUrlCache[$categoryId];
            }
        }

        return $result;
    }

    /**
     * @param $content
     * @param $fileName
     * @param false $deleteOldFile
     */
    private function writeToFile($content, $fileName, $deleteOldFile = false): void
    {
        try {
            $varFolder = $this->directoryList->getPath(DirectoryList::VAR_DIR);
            $filePath = sprintf('%s%s%s', $varFolder, DIRECTORY_SEPARATOR, $fileName);
            if ($this->file->isExists($filePath) && $deleteOldFile) {
                $this->file->deleteFile($filePath);
            }

            $file = $this->file->fileOpen($filePath, 'a');
            $this->file->fileWrite($file, $content);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Could not write to file: %s', $e->getMessage()));
        }
    }
}
