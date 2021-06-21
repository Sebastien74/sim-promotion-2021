<?php

namespace App\Entity;

use App\Entity\Security\Group;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseSecurity
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
abstract class BaseSecurity extends BaseInterface implements UserInterface
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("main")
     */
    protected $login;

    /**
     * @ORM\Column(type="string", length=180)
     * @Groups("main")
     * @Assert\Email()
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("main")
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("main")
     */
    protected $firstName;

    /**
     * @var string
     *
     * @Assert\Length(max=4096)
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $password;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = true;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $confirmEmail = true;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $agreeTerms = false;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $agreesTermsAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $tokenRequest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $locale;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $lastLogin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $lastActivity;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $resetPassword = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @var DateTime
     */
    protected $resetPasswordDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $secretKey;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $alerts = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\Group", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $group;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->secretKey = md5(uniqid() . $this->login);
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = [];

        /* @var Group $group */
        $group = $this->group;

        foreach ($group->getRoles() as $role) {
            $roles[] = $role->getName();
        }

        return array_unique($roles);
    }

    /**
     * Generate avatar
     *
     * @param int $size
     * @param string $type
     * @return string|null
     */
    public function getAvatar(int $size = 30, $type = 'initialTxt'): string
    {
        if ($type === 'initialTxt') {

            $code = $this->firstName . ' ' . $this->lastName;
            if (trim($code) == ' ' || $code == ' ' || empty($code)) {
                $code = $this->login;
            }

            $initials = '';
            $matches = explode(' ', $code);
            foreach ($matches as $match) {
                $initials .= substr($match, 0, 1);
            }

            return strtoupper(Urlizer::urlize(substr($initials, 0, 2)));
        } elseif ($type === 'initial') {

            $code = $this->firstName . '+' . $this->lastName;
            if (trim($code) === '+') {
                $code = $this->login;
            }

            return 'https://eu.ui-avatars.com/api/?name=' . $code . '&background=58a2d9&color=fff&font-size=0.33&size=' . $size;
        } else {

            $url = 'https://robohash.org/' . $this->email . sprintf('?size=%dx%d', $size, $size);

            if ($type === 'robot') {
                return $url . '&set=set1';
            } elseif ($type === 'monster') {
                return $url . '&set=set2';
            } elseif ($type === 'robot-head') {
                return $url . '&set=set3';
            } elseif ($type === 'cat') {
                return $url . '&set=set4';
            }
        }
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        $username = $this->login;

        if (strlen($this->lastName) > 0 && strlen($this->firstName)) {
            $username = $this->lastName . ' ' . $this->firstName;
        }

        return (string)$username;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * @see UserInterface
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using bcrypt or argon
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getConfirmEmail(): ?bool
    {
        return $this->confirmEmail;
    }

    public function setConfirmEmail(bool $confirmEmail): self
    {
        $this->confirmEmail = $confirmEmail;

        return $this;
    }

    public function getAgreesTermsAt(): ?DateTimeInterface
    {
        return $this->agreesTermsAt;
    }

    public function setAgreesTermsAt(DateTimeInterface $agreesTermsAt): self
    {
        $this->agreesTermsAt = $agreesTermsAt;

        return $this;
    }

    public function getAgreeTerms(): ?bool
    {
        return $this->agreeTerms;
    }

    public function setAgreeTerms(bool $agreeTerms): self
    {
        $this->agreeTerms = $agreeTerms;

        return $this;
    }

    public function agreeTerms(): bool
    {
        $this->agreeTerms = true;
        $this->agreesTermsAt = new DateTime();

        return $this->agreeTerms;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenRequest(): ?DateTimeInterface
    {
        return $this->tokenRequest;
    }

    public function setTokenRequest(?DateTimeInterface $tokenRequest): self
    {
        $this->tokenRequest = $tokenRequest;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLastLogin(): ?DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastActivity(): ?DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function setLastActivity(?DateTimeInterface $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function getResetPassword(): ?bool
    {
        return $this->resetPassword;
    }

    public function setResetPassword(bool $resetPassword): self
    {
        $this->resetPassword = $resetPassword;

        return $this;
    }

    public function getResetPasswordDate(): ?DateTimeInterface
    {
        return $this->resetPasswordDate;
    }

    public function setResetPasswordDate(DateTimeInterface $resetPasswordDate): self
    {
        $this->resetPasswordDate = $resetPasswordDate;

        return $this;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    public function getAlerts(): ?array
    {
        return $this->alerts;
    }

    public function setAlerts(?array $alerts): self
    {
        $this->alerts = $alerts;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }
}