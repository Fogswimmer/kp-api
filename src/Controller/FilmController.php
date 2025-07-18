<?php

namespace App\Controller;

use App\Dto\Common\FileNameSearchDto;
use App\Dto\Common\LocaleDto;
use App\Dto\Entity\Assessment\AssessmentDto;
use App\Dto\Entity\Film\FilmDto;
use App\Dto\Entity\Film\FilmQueryDto;
use App\Entity\User;
use App\Exception\NotFound\FilmNotFoundException;
use App\Model\Response\Entity\Film\FilmDetail;
use App\Model\Response\Entity\Film\FilmForm;
use App\Model\Response\Entity\Film\FilmList;
use App\Model\Response\Entity\Film\FilmPaginateList;
use App\Service\Entity\FilmService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Tag(name: 'Film')]
class FilmController extends AbstractController
{
    public function __construct(
        private FilmService $filmService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check films presence in the DB.
     */
    #[Route(
        path: 'api/films/check',
        name: 'api_film_check',
        methods: ['GET']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
    )]
    public function checkEmpty(): Response
    {
        return $this->json(
            ['present' => $this->filmService->checkFilmsPresence()]
        );
    }

    /**
     * Find a film by slug.
     */
    #[
        Route(
            path: '/api/films/get/{slug}',
            name: 'api_film',
            methods: ['GET'],
            requirements: ['slug' => '[a-z0-9-]+']
        )
    ]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: FilmDetail::class)
    )]
    public function find(string $slug, #[MapQueryString] LocaleDto $dto): Response
    {
        $status = Response::HTTP_OK;
        $data = null;

        try {
            $data = $this->filmService->get($slug, $dto->locale);
        } catch (FilmNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $this->logger->error($e);
        }

        return $this->json($data, $status);
    }

    /**
     * Find a film form by slug.
     */
    #[
        Route(
            path: '/api/films/{slug}/form',
            name: 'api_film_form',
            methods: ['GET'],
            requirements: ['slug' => '[a-z0-9-]+']
        )
    ]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: FilmForm::class)
    )]
    public function findForm(string $slug): Response
    {
        $status = Response::HTTP_OK;
        $data = null;

        try {
            $data = $this->filmService->findForm($slug);
        } catch (FilmNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $this->logger->error($e);
        }

        return $this->json($data, $status);
    }

    /**
     * Find 6 latest films.
     */
    #[Route(
        path: '/api/films-latest',
        name: 'api_film_latest',
        methods: ['GET'],
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: FilmList::class)
    )]
    public function latest(): Response
    {
        $status = Response::HTTP_OK;
        $data = null;
        $count = 5;
        try {
            $data = $this->filmService->latest($count);
        } catch (FilmNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $this->logger->error($e);
        }

        return $this->json($data, $status);
    }

    /**
     * Find films with similar genres.
     */
    #[Route(
        path: '/api/films/{slug}/similar-genres',
        name: 'api_film_similar_genre',
        requirements: ['slug' => '[a-z0-9-]+'],
        methods: ['GET'],
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: FilmList::class)
    )]
    public function similarGenres(string $slug): Response
    {
        $status = Response::HTTP_OK;
        $data = null;
        $count = 5;
        try {
            $data = $this->filmService->similarGenres($slug, $count);
        } catch (FilmNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $this->logger->error($e);
        }

        return $this->json($data, $status);
    }

    /**
     * Find 5 top films.
     */
    #[Route(
        path: '/api/films-top',
        name: 'api_film_top',
        methods: ['GET'],
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: FilmList::class)
    )]
    public function top(): Response
    {
        $status = Response::HTTP_OK;
        $data = null;
        $count = 5;
        try {
            $data = $this->filmService->top($count);
        } catch (FilmNotFoundException $e) {
            $status = Response::HTTP_NOT_FOUND;
            $this->logger->error($e);
        }

        return $this->json($data, $status);
    }

    /**
     * Filter films by query params.
     */
    #[Route(
        path: 'api/films/filter',
        name: 'api_film_filter',
        methods: ['GET']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: FilmPaginateList::class)
    )]
    public function filter(#[MapQueryString] ?FilmQueryDto $dto = new FilmQueryDto()): Response
    {
        return $this->json($this->filmService->filter($dto));
    }

    /**
     * Create a new film.
     */
    #[Route(
        path: 'api/films',
        name: 'api_film_create',
        methods: ['POST']
    )]
    #[OA\Response(
        response: 200,
        description: 'A new film has been created',
        content: new Model(type: FilmForm::class)
    )]
    #[OA\Response(response: 500, description: 'An error occurred while creating the film')]
    public function create(
        #[MapRequestPayload] ?FilmDto $dto,
        #[CurrentUser] User $user
    ): Response {
        $data = null;
        $status = Response::HTTP_OK;

        try {
            $data = $this->filmService->create($dto, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }

    /**
     * Update a film by id.
     */
    #[Route(
        path: 'api/films/{id}',
        name: 'api_film_update',
        methods: ['POST', 'PUT']
    )]
    #[OA\Response(
        response: 200,
        description: 'A film has been updated',
        content: new Model(type: FilmForm::class)
    )]
    #[OA\Response(response: 500, description: 'An error occurred while updating the film')]
    public function update(
        int $id,
        #[MapRequestPayload] ?FilmDto $dto,
        #[MapQueryString] LocaleDto $localeDto
    ): Response {
        $data = null;
        $status = Response::HTTP_OK;

        try {
            $data = $this->filmService->update($id, $dto, $localeDto->locale);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }

    /**
     * Delete a film by id.
     */
    #[Route(
        path: 'api/films/{id}',
        name: 'api_film_delete',
        methods: ['DELETE']
    )]
    #[OA\Response(
        response: 200,
        description: 'The film has been deleted',
    )]
    #[OA\Response(response: 500, description: 'An error occurred while deleting the film')]
    public function delete(
        int $id,
    ): Response {
        $data = null;
        $status = Response::HTTP_OK;

        try {
            $this->filmService->delete($id);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }

    /**
     * Upload a gallery for a film.
     */
    #[Route(
        path: 'api/films/{id}/gallery',
        name: 'api_film_gallery_upload',
        methods: ['POST']
    )]
    #[RequestBody(
        content: [
            new MediaType(
                mediaType: 'multipart/form-data',
                schema: new Schema(properties: [
                    new OA\Property(
                        property: 'gallery',
                        type: 'file',
                    ),
                ])
            ),
        ]
    )]
    #[OA\Response(response: 500, description: 'An error occurred while uploading the gallery')]
    public function uploadGallery(
        int $id,
        Request $request,
    ): Response {
        $data = null;
        $status = Response::HTTP_OK;

        try {
            $files = $request->files->get('gallery');
            if (null === $files) {
                $status = Response::HTTP_BAD_REQUEST;
                $data = 'No files found in request. Did you forget to specify the formdata key "gallery"?';

                return $this->json($data, $status);
            }
            $data = $this->filmService->uploadGallery($id, $files);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }

    /**
     * Delete a picture or pictures from the film gallery by file name(s).
     */
    #[Route(
        path: 'api/films/{id}/gallery',
        name: 'api_film_gallery_delete',
        methods: ['DELETE']
    )]
    #[OA\Response(
        response: 200,
        description: 'Picture(s) deleted',
    )]
    #[OA\Response(response: 500, description: 'An error occurred while deleteing the picture(s)')]
    public function deleteGalleryPicture(
        int $id,
        #[MapRequestPayload] ?FileNameSearchDto $dto,
    ): Response {
        $data = null;
        $status = Response::HTTP_OK;

        try {
            if (empty($dto->fileNames)) {
                $status = Response::HTTP_BAD_REQUEST;
                $data = 'The request is empty. No file names found.';

                return $this->json($data, $status);
            }
            $data = $this->filmService->deleteFromGallery($id, $dto->fileNames);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }

    #[Route(
        path: 'api/films/{id}/assess',
        name: 'api_film_assess',
        methods: ['POST', 'PUT']
    )]
    public function assess(
        int $id,
        #[MapRequestPayload] ?AssessmentDto $dto,
        #[MapQueryString] LocaleDto $localedto
    ): Response {
        $data = null;
        $status = Response::HTTP_OK;
        try {
            $user = $this->getUser();
            $data = $this->filmService->assess($id, $dto, $localedto->locale, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }

    #[Route(
        path: 'api/films/{filmId}/assess/{assessmentId}',
        name: 'api_film_assessment_delete',
        methods: ['DELETE']
    )]
    public function deleteAssessment(
        int $filmId,
        int $assessmentId,
    ): Response {
        $data = null;
        $status = Response::HTTP_OK;
        try {
            $user = $this->getUser();
            $data = $this->filmService->deleteAssessment($filmId, $assessmentId, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e);
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }
}
