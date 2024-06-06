<?php

namespace App\Controller\Admin\Listing;

use App\Entity\Listing;
use App\Repository\ListingRepository;
use App\Repository\ListingCategoryRepository;
use App\Repository\ListingImageRepository;
use App\Repository\ListingStatusRepository;
use App\Repository\ListingTypeRepository;
use App\Repository\ProfileRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;



#[Route('/admin/listing')]
class AdminListingController extends AbstractController
{

    #[Route('/', name: 'app_listing_index', methods: ['GET'])]
    public function getAll(ListingRepository $listingRepository, ListingImageRepository $listingImageRepository): JsonResponse
    {
        $listings = $listingRepository->findAll();
        $data = [];
   
        foreach ($listings as $listing) {
            $data[] = [
                'id' => $listing->getId(),
                'city' => $listing->getCity(),
                'country' => $listing->getCountry(),
                'description' => $listing->getDescription(),
                'fkListingCategory' => $listing->getFkListingCategory()->getCategory(),
                'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
                'fkListingType' => $listing->getFkListingType()->getType(),
                'name' => $listing->getFkProfile()->getPseudo(),
                'createdAt' => $listing->getFkProfile()->getCreatedAt(),
                'photo' => $listing->getFkProfile()->getFkAvatar()->getImage(),
                'postCode' => $listing->getPostcode(),
                'title' => $listing->getTitle(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/images', name: 'app_listing_images', methods: ['GET'])]
    public function getAllListingsWithImages(ListingRepository $listingRepository, ListingImageRepository $listingImageRepository): JsonResponse
    {
        $listings = $listingRepository->findAll();
        $data = [];

        foreach ($listings as $listing) {

            $listingImage = $listingImageRepository->findBy(array('fkListing' => $listing->getId()));

            foreach($listingImage as $tt){
                $images[] = $tt->getImage();
            }
        
            if($listingImage){

                $data[] = [
                    'id' => $listing->getId(),
                    'city' => $listing->getCity(),
                    'country' => $listing->getCountry(),
                    'description' => $listing->getDescription(),
                    'fkListingCategory' => $listing->getFkListingCategory()->getId(),
                    'fkListingStatus' => $listing->getFkListingStatus()->getId(),
                    'fkListingType' => $listing->getFkListingType()->getType(),
                    'name' => $listing->getFkProfile()->getPseudo(),
                    'createdAt' => $listing->getFkProfile()->getCreatedAt(),
                    'photo' => $listing->getFkProfile()->getFkAvatar()->getImage(),
                    'postCode' => $listing->getPostcode(),
                    'title' => $listing->getTitle(),
                    'Images' => $images
                    ];

            }
          
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }
    #[Route('/search', name: 'app_listing_search__index', methods: ['POST'])]
    public function getCustomListings(Request $request,ListingRepository $listingRepository, ListingImageRepository $listingImageRepository, ListingCategoryRepository $listingCategoryRepository,ManagerRegistry $doctrine): JsonResponse
    {
        $sendedData = json_decode($request->getContent(), true);

        $fkListingCategory = $sendedData['fkListingCategory'];
        $latitude = $sendedData['latitude'];
        $longitude = $sendedData['longitude'];
        $round = $sendedData['round'];
        $fkListingType= $sendedData['fkListingType'];
  
        $em = $doctrine->getManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('listing')
           ->from('App\Entity\Listing', 'listing')
           ->where($qb->expr()->between('listing.latitude', ':min_lat', ':max_lat'))
           ->andWhere($qb->expr()->between('listing.longitude', ':min_lng', ':max_lng'))
           ->andWhere('listing.fkListingCategory = :category')
           ->andWhere('listing.fkListingType = :type')
           ->andWhere('listing.fkListingStatus = 1')

           ->setParameter('min_lat', $latitude - ($round/111))
           ->setParameter('max_lat', $latitude + ($round/111))
           ->setParameter('min_lng', $longitude - ($round/76))
           ->setParameter('max_lng', $longitude + ($round/76))
           ->setParameter('category', $fkListingCategory)
           ->setParameter('type', $fkListingType);

        
        $listings = $qb->getQuery()->getResult();
            $data = [];
            $arrayImages = [];
        
            foreach ($listings as $listing) {
                $images = $listingImageRepository->findBy(array('fkListing' => $listing->getId()));
                for($i=0; $i<sizeof($images); $i++){
                    $arrayImages[$i] = $images[$i]->getImage();
                }
                    $data[] = [
                        'id' => $listing->getId(),
                        'city' => $listing->getCity(),
                        'country' => $listing->getCountry(),
                        'description' => $listing->getDescription(),
                        'fkListingCategory' => $listing->getFkListingCategory()->getCategory(),
                        'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
                        'fkListingType' => $listing->getFkListingType()->getType(),
                        'fkProfile' => $listing->getFkProfile()->getId(),
                        'photo' => $listing->getFkProfile()->getFkAvatar()->getImage(),
                        'createdAt' => $listing->getFkProfile()->getCreatedAt(),
                        'name' => $listing->getFkProfile()->getPseudo(),
                        'postCode' => $listing->getPostcode(),
                        'latitude' => $listing->getLatitude(),
                        'longitude' => $listing->getLongitude(),
                        'title' => $listing->getTitle(),
                        'image' => $arrayImages,
                        'fkListingId' => $listing->getId(),
                    ];
                
            }
    

        return new JsonResponse($data, Response::HTTP_OK);
    }






    #[Route('/images/category/{id}', name: 'app_listing_images_category__index', methods: ['GET'])]
    public function getAllListingsWithImagesByCategory($id,ListingRepository $listingRepository, ListingImageRepository $listingImageRepository): JsonResponse
    {
        $listings = $listingRepository->findBy(array('fkListingCategory' => $id));
        $listingImages = $listingImageRepository->findAll();
        $data = [];

        foreach ($listingImages as $listingImage) {
        
            foreach ($listings as $listing) {
                if($listing->getId() == $listingImage->getFkListing()->getId()){
                    $data[] = [
                        'id' => $listing->getId(),
                        'city' => $listing->getCity(),
                        'country' => $listing->getCountry(),
                        'description' => $listing->getDescription(),
                        'fkListingCategory' => $listing->getFkListingCategory()->getId(),
                        'fkListingStatus' => $listing->getFkListingStatus()->getId(),
                        'fkListingType' => $listing->getFkListingType()->getType(),
                        'name' => $listing->getFkProfile()->getPseudo(),
                        'createdAt' => $listing->getFkProfile()->getCreatedAt(),
                        'photo' => $listing->getFkProfile()->getFkAvatar()->getImage(),
                        'postCode' => $listing->getPostcode(),
                        'title' => $listing->getTitle(),
                        'idImage' => $listingImage->getId(),
                        'image' => $listingImage->getImage(),
                        'createdAt' => $listingImage->getCreatedAt(),
                        'fkListingId' => $listingImage->getFkListing()->getId(),
                    ];
                }
            }
            
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/me/{id}', name: 'app_listing_show_mine', methods: ['GET'])]
    public function getMyListings($id, listingRepository $listingRepository, ListingImageRepository $listingImageRepository, SubCategoryRepository $subCategoryRepository): JsonResponse
    {
        $listings = $listingRepository->findBy(array('fkProfile' => $id));
        
        $data = [];


        foreach ($listings as $listing) {
            $subCategory = $subCategoryRepository->findOneBy(array('fkListingCategory' => $listing->getFkListingCategory()->getId()));
            $image = $listingImageRepository->findOneBy(array('fkListing' => $id));
    
            $data[] = [
                'id' => $listing->getId(),
                'city' => $listing->getCity(),
                'country' => $listing->getCountry(),
                'description' => $listing->getDescription(),
                'fkListingCategory' => $listing->getFkListingCategory()->getCategory(),
                'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
                'fkListingType' => $listing->getFkListingType()->getId(),
                'fkListingType' => $listing->getFkListingType()->getType(),
                'name' => $listing->getFkProfile()->getPseudo(),
                'createdAt' => $listing->getFkProfile()->getCreatedAt(),
                'photo' => $listing->getFkProfile()->getFkAvatar()->getImage(),

                'postCode' => $listing->getPostcode(),
                'title' => $listing->getTitle(),
                'subCategory' => $subCategory->getSubcategory(),
                'image' => $image,
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_listing', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, ListingCategoryRepository $listingCategoryRepository, 
    ListingStatusRepository $listingStatusRepository, ListingTypeRepository $listingTypeRepository , ProfileRepository $profileRepository
    ): JsonResponse
    {

        $entityManager = $doctrine->getManager();

        $data = json_decode($request->getContent(), true);
        $city = $data['city'];
        $country = $data['country'];
        $longitude = $data['longitude'];
        $latitude = $data['latitude'];
        $description = $data['description'];
        $title = $data['title'];
        $fkListingCategory = $data['fkListingCategory'];
        $fkListingStatus = $data['fkListingStatus'];
        $fkListingType= $data['fkListingType'];
        $fkProfile = $data['fkProfile'];
        $postCode = $data['postCode'];

        if (empty($city) || empty($country) ||empty($description)
            ||empty($title) ||empty($fkListingCategory) ||empty($fkListingStatus)

            || empty($fkListingType) ||empty($fkProfile) 
            ||empty($postCode) ) {

            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
        $newListing = new Listing();

        $category = $listingCategoryRepository->find($fkListingCategory);
        $status = $listingStatusRepository->find($fkListingStatus);
        $type = $listingTypeRepository->find($fkListingType);
        $profile = $profileRepository->find($fkProfile);
        

        $newListing->setCity($city);
        $newListing->setCountry($country);
        $newListing->setDescription($description);
        $newListing->setFkListingCategory($category);
        $newListing->setFkListingStatus($status);
        $newListing->setFkListingType($type);
        $newListing->setFkProfile($profile);

        $newListing->setPostcode($postCode);

        $newListing->setTitle($title);
        $newListing->setLatitude($latitude);
        $newListing->setLongitude($longitude);



        $entityManager->persist($newListing);
        $entityManager->flush();
   
        return $this->json('Created new listing successfully with id ' . $newListing->getId());
    }


    #[Route('/{id}', name: 'app_listing_show', methods: ['GET'])]
    public function get($id, listingRepository $listingRepository, ListingImageRepository $listingImageRepository, SubCategoryRepository $subCategoryRepository): JsonResponse
    {
        $listing = $listingRepository->find($id);
        $images = $listingImageRepository->findBy(array('fkListing' => $id));

        $arrayImages = [];

        for($i=0; $i<sizeof($images); $i++){
            $arrayImages[$i] = $images[$i]->getImage();
        }

        $subCategory = $subCategoryRepository->findOneBy(array('fkListingCategory' => $listing->getFkListingCategory()->getId()));



        $data[] = [
            'id' => $listing->getId(),
            'city' => $listing->getCity(),
            'country' => $listing->getCountry(),
            'description' => $listing->getDescription(),
            'fkListingCategory' => $listing->getFkListingCategory()->getCategory(),
            'fkListingCategoryImage' => $listing->getFkListingCategory()->getCategoryImage(),
            'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
            'fkListingType' => $listing->getFkListingType()->getType(),
            'name' => $listing->getFkProfile()->getPseudo(),
            'createdAt' => $listing->getFkProfile()->getCreatedAt(),
            'photo' => $listing->getFkProfile()->getFkAvatar()->getImage(),
            'title' => $listing->getTitle(),
            'subCategory' => $subCategory->getSubcategory(),
            'images' => $arrayImages,
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

  

    #[Route('/{id}/edit', name: 'app_listing_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, listingRepository $listingRepository, EntityManagerInterface $manager, ListingCategoryRepository $listingCategoryRepository, 
    ListingStatusRepository $listingStatusRepository, ListingTypeRepository $listingTypeRepository , ProfileRepository $profileRepository, ListingImageRepository $listingImageRepository
    ): JsonResponse
    {
        $listing = $listingRepository->find($id);
        $data = json_decode($request->getContent(), true);

        $arrayImages = [];

        $images = $listingImageRepository->findBy(array('fkListing' => $id));

        foreach ($images as $image) {
            $arrayImages[] = [
                'image' => $image->getImage(),
            ];
        }

        empty($data['city']) ? true : $listing->setCity($data['city']);
        empty($data['country']) ? true : $listing->setCountry($data['country']);
        empty($data['description']) ? true : $listing->setDescription($data['description']);
        empty($data['postCode']) ? true : $listing->setPostcode($data['postCode']);
        empty($data['title']) ? true : $listing->setTitle($data['title']);
        empty($data['fkListingCategory']) ? true : $listing->setFkListingCategory($listingCategoryRepository->find($data['fkListingCategory']));
        empty($data['fkListingStatus']) ? true : $listing->setFkListingStatus($listingStatusRepository->find($data['fkListingStatus']));
        empty($data['fkListingType']) ? true : $listing->setFkListingType($listingTypeRepository->find($data['fkListingType']));
        empty($data['fkProfile']) ? true : $listing->setFkProfile($profileRepository->find($data['fkProfile']));


        $manager->persist($listing);
        $manager->flush();


        $listingArray= [
            'id' => $listing->getId(),
            'city' => $listing->getCity(),
            'country' => $listing->getCountry(),
            'description' => $listing->getDescription(),
            'fkListingCategory' => $listing->getFkListingCategory()->getId(),
            'fkListingStatus' => $listing->getFkListingStatus()->getId(),
            'fkListingType' => $listing->getFkListingType()->getId(),
            'fkProfile' => $listing->getFkProfile()->getId(),
            'postCode' => $listing->getPostcode(),
            'title' => $listing->getTitle(),
            'images' => $arrayImages,
        ];

        return new JsonResponse($listingArray, Response::HTTP_OK);
    }




    #[Route('/{id}', name: 'app_listing_delete', methods: ['DELETE'])]
    public function delete($id, listingRepository $listingRepository, Request $request): JsonResponse
    {
        $listing = $listingRepository->find($id);

        // if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $listingRepository->remove($listing, true);
            return new JsonResponse(['status' => 'listing deleted'], Response::HTTP_NO_CONTENT);
        // }
        // return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }
}