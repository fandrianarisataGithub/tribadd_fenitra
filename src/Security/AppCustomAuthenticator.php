<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class AppCustomAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $router;
    /**
     * @var UserPasswordEncoder
     */
    private $userPasswordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, RouterInterface $router, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->router = $router;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function supports(Request $request)
    {
        return 'login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }
    /*public function captchaverify($recaptcha){
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret"=>"6LdnoP8ZAAAAAO0tguwmyoDYF-N8SOR6sA5R_jbr","response"=>$recaptcha));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }*/
    public function getCredentials(Request $request)
    {
        $capv = $request->request->get('g-recaptcha-response');
        // $response = $this->captchaverify($capv);
            $credentials = [
                'email' => $request->request->get('_username'),
                'password' => sha1($request->request->get('_password')),
                'path' => sha1($request->request->get('path')),
                'csrf_token' => $request->request->get('_csrf_token'),
            ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
            /*if($response == true){
                return $credentials;
            }else{
                return [
                    'email' => $request->request->get('_username'),
                    'password' => 'null',
                    'csrf_token' => $request->request->get('_csrf_token'),
                ];
            }*/
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        // $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        $qb = $this->entityManager->createQueryBuilder();
        $user = $qb->select('u')
            ->from(User::class, 'u')
            ->where('u.email LIKE :email OR u.username LIKE :email')
            ->setParameter('email', $credentials['email'])
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->userPasswordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $path = $request->get('path');
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        if(isset($path) and $path != null){
            return new RedirectResponse($path);
        }
        return new RedirectResponse($this->router->generate('home'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('login');
    }

    /**
     * Code réinitialisation
     *
     * @param string $email
     * @return string
     */
    public static function getCode($email)
    {
        return md5(md5($email));
    }

    /**
     * Vérification de validité
     *
     * @param string $email
     * @param string $code
     * @return boolean
     */
    public static function isValidCode($email, $code)
    {
        return ($code == self::getCode($email));
    }
}
