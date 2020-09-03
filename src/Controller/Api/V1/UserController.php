<?php
declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Orm\Entity\Authentication;
use App\Orm\Entity\User;
use App\Orm\Repository\UserRepository;
use App\Struct\InvalidFieldStruct;
use App\Struct\NewUserStruct;
use App\Struct\UserInfoStruct;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/v1/user")
 */
class UserController
{
    private $token_storage;
    private $user_repository;
    private $entity_manager;
    private $validator;

    public function __construct(TokenStorageInterface $token_storage, UserRepository $user_repository, EntityManagerInterface $entity_manager, ValidatorInterface $validator)
    {
        $this->token_storage = $token_storage;
        $this->user_repository = $user_repository;
        $this->entity_manager = $entity_manager;
        $this->validator = $validator;
    }

    /**
     * @Route("/me", methods={"GET"})
     * @IsGranted("SCOPE_INFO", message="'info' scope required")
     * @SWG\Get(
     *     description="Get the user information.",
     *     responses={
     *         @SWG\Response(response=200, description="Return the user information.", @SWG\Schema(ref=@Model(type=UserInfoStruct::class))),
     *         @SWG\Response(response=401, description="If the user is not authenticated.")
     *     },
     *     security={{"password":{}}, {"client_credentials":{}}}
     * )
     */
    public function indexAction(): JsonResponse
    {
        $user = $this->user_repository->findOneByEmail($this->token_storage->getToken()->getUsername());

        return new JsonResponse(UserInfoStruct::fromUser($user));
    }

    /**
     * @Route("/register", methods={"POST"})
     * @SWG\Post(
     *     description="Create a new user account",
     *     parameters={
     *         @SWG\Parameter(
     *             name="body",
     *             in="body",
     *             description="User information used to create an account.",
     *             required=true,
     *             @SWG\Schema(ref=@Model(type=NewUserStruct::class)),
     *         )
     *     },
     *     responses={
     *         @SWG\Response(response=200, description="Return the created user information.", @SWG\Schema(ref=@Model(type=UserInfoStruct::class))),
     *         @SWG\Response(response=400, description="If the information is not valid.", @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=InvalidFieldStruct::class))))
     *     }
     * )
     */
    public function registerAction(Request $request): JsonResponse
    {
        if (false === ($json = json_decode($request->getContent(), true))) {
            return new JsonResponse(['error' => 'unable to json decode body'], 500);
        }

        $data = NewUserStruct::fromArray($json);

        $violations = $this->validator->validate($data);

        if (count($violations) > 0) {
            return new JsonResponse(array_map(function (ConstraintViolationInterface $violation) {
                return InvalidFieldStruct::fromViolation($violation);
            }, iterator_to_array($violations)), 400);
        }

        $user = new User($data->email, new Authentication($data->password));

        $this->entity_manager->persist($user);
        $this->entity_manager->flush();

        return new JsonResponse(UserInfoStruct::fromUser($user));
    }
}
