<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\EventListener\MessageListener;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageListenerTest extends TestCase
{
    /**
     * @dataProvider provideHeaders
     */
    public function testHeaders(Headers $initialHeaders, Headers $defaultHeaders, Headers $expectedHeaders, array $rules = MessageListener::DEFAULT_RULES)
    {
        $message = new Message($initialHeaders);
        $listener = new MessageListener($defaultHeaders, null, $rules);
        $event = new MessageEvent($message, new Envelope(new Address('sender@example.com'), [new Address('recipient@example.com')]), 'smtp');
        $listener->onMessage($event);

        $this->assertEquals($expectedHeaders, $event->getMessage()->getHeaders());
    }

    public static function provideHeaders(): iterable
    {
        $initialHeaders = new Headers();
        $defaultHeaders = (new Headers())
            ->add(new MailboxListHeader('from', [new Address('from-default@example.com')]))
        ;
        yield 'No defaults, all headers copied over' => [$initialHeaders, $defaultHeaders, $defaultHeaders];

        $initialHeaders = new Headers();
        $defaultHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'bar'))
            ->add(new UnstructuredHeader('bar', 'foo'))
        ;
        yield 'No defaults, default is to set if empty' => [$initialHeaders, $defaultHeaders, $defaultHeaders];

        $initialHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'initial'))
        ;
        $defaultHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'bar'))
            ->add(new UnstructuredHeader('bar', 'foo'))
        ;
        $expectedHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'initial'))
            ->add(new UnstructuredHeader('bar', 'foo'))
        ;
        yield 'Some defaults, default is to set if empty' => [$initialHeaders, $defaultHeaders, $expectedHeaders];

        $initialHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'initial'))
        ;
        $defaultHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'bar'))
            ->add(new UnstructuredHeader('bar', 'foo'))
        ;
        $rules = [
            'foo' => MessageListener::HEADER_REPLACE,
        ];
        yield 'Some defaults, replace if set' => [$initialHeaders, $defaultHeaders, $defaultHeaders, $rules];

        $initialHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'bar'))
        ;
        $defaultHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'foo'))
        ;
        $expectedHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'bar'))
            ->add(new UnstructuredHeader('foo', 'foo'))
        ;
        $rules = [
            'foo' => MessageListener::HEADER_ADD,
        ];
        yield 'Some defaults, add if set (not unique header)' => [$initialHeaders, $defaultHeaders, $expectedHeaders, $rules];

        $initialHeaders = (new Headers())
            ->add(new MailboxListHeader('bcc', [new Address('bcc-initial@example.com')]))
        ;
        $defaultHeaders = (new Headers())
            ->add(new MailboxListHeader('bcc', [new Address('bcc-default@example.com'), new Address('bcc-default-1@example.com')]))
        ;
        $expectedHeaders = (new Headers())
            ->add(new MailboxListHeader('bcc', [new Address('bcc-initial@example.com'), new Address('bcc-default@example.com'), new Address('bcc-default-1@example.com')]))
        ;
        yield 'bcc, add another bcc (unique header)' => [$initialHeaders, $defaultHeaders, $expectedHeaders];

        $initialHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'initial'))
        ;
        $defaultHeaders = (new Headers())
            ->add(new UnstructuredHeader('foo', 'bar'))
            ->add(new UnstructuredHeader('bar', 'foo'))
        ;
        $rules = [
            'Foo' => MessageListener::HEADER_REPLACE,
        ];
        yield 'Capitalized header rule (case-insensitive), replace if set' => [$initialHeaders, $defaultHeaders, $defaultHeaders, $rules];
    }

    public function testTranslatableSubject()
    {
        if (!method_exists(TemplatedEmail::class, 'getTranslatableSubject')) {
            $this->markTestSkipped('symfony/twig-bridge 7.3 or higher required');
        }

        $message = new TemplatedEmail();
        $message->subject(new TranslatableMessage('hello.world'));
        $listener = new MessageListener(translator: new class implements TranslatorInterface {
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return 'Hello World';
            }

            public function getLocale(): string
            {
                return 'en';
            }
        });
        $event = new MessageEvent($message, new Envelope(new Address('sender@example.com'), [new Address('recipient@example.com')]), 'smtp');
        $listener->onMessage($event);

        $this->assertSame('Hello World', $message->getSubject());
    }
}
