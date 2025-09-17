import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Mail, MapPin, Phone, Users } from 'lucide-react';

interface Agent {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    bio: string | null;
    license_number: string | null;
    specializations: string | null;
    years_experience: number;
    languages_spoken: string | null;
    avatar_url: string | null;
    social_facebook: string | null;
    social_twitter: string | null;
    social_linkedin: string | null;
    social_instagram: string | null;
    office_address: string | null;
    office_phone: string | null;
    is_active: boolean;
    properties_count: number;
    created_at: string;
    updated_at: string;
}

interface AgentsShowProps {
    agent: Agent;
}

export default function AgentsShow({ agent }: AgentsShowProps) {
    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Agents', href: '/admin/agents' },
        { title: agent.name, href: `/admin/agents/${agent.id}` },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Agent: ${agent.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{agent.name}</h1>
                        <p className="text-muted-foreground">
                            Agent details and information
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" asChild>
                            <Link href="/admin/agents">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Agents
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={`/admin/agents/${agent.id}/edit`}>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Agent
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Basic Information</CardTitle>
                                <CardDescription>
                                    Personal and professional details
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Full Name</label>
                                        <p className="text-sm">{agent.name}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Email</label>
                                        <p className="text-sm flex items-center">
                                            <Mail className="h-4 w-4 mr-2" />
                                            {agent.email}
                                        </p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Phone</label>
                                        <p className="text-sm flex items-center">
                                            <Phone className="h-4 w-4 mr-2" />
                                            {agent.phone || 'Not provided'}
                                        </p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">License Number</label>
                                        <p className="text-sm">{agent.license_number || 'Not provided'}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Years of Experience</label>
                                        <p className="text-sm">{agent.years_experience} years</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Languages</label>
                                        <p className="text-sm">{agent.languages_spoken || 'Not specified'}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Status</label>
                                        <Badge variant={agent.is_active ? 'default' : 'secondary'}>
                                            {agent.is_active ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Properties</label>
                                        <p className="text-sm flex items-center">
                                            <Users className="h-4 w-4 mr-2" />
                                            {agent.properties_count} properties
                                        </p>
                                    </div>
                                </div>

                                {agent.bio && (
                                    <>
                                        <Separator />
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">Biography</label>
                                            <p className="text-sm mt-1">{agent.bio}</p>
                                        </div>
                                    </>
                                )}

                                {agent.specializations && (
                                    <>
                                        <Separator />
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">Specializations</label>
                                            <p className="text-sm mt-1">{agent.specializations}</p>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        {agent.avatar_url && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Profile Photo</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <img
                                        src={agent.avatar_url}
                                        alt={agent.name}
                                        className="w-full h-48 object-cover rounded-lg"
                                    />
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle>Office Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {agent.office_phone && (
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Office Phone</label>
                                        <p className="text-sm flex items-center">
                                            <Phone className="h-4 w-4 mr-2" />
                                            {agent.office_phone}
                                        </p>
                                    </div>
                                )}
                                {agent.office_address && (
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Office Address</label>
                                        <p className="text-sm flex items-start">
                                            <MapPin className="h-4 w-4 mr-2 mt-0.5" />
                                            {agent.office_address}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {(agent.social_facebook || agent.social_twitter || agent.social_linkedin || agent.social_instagram) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Social Media</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    {agent.social_facebook && (
                                        <a
                                            href={agent.social_facebook}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="block text-sm text-blue-600 hover:underline"
                                        >
                                            Facebook
                                        </a>
                                    )}
                                    {agent.social_twitter && (
                                        <a
                                            href={agent.social_twitter}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="block text-sm text-blue-600 hover:underline"
                                        >
                                            Twitter
                                        </a>
                                    )}
                                    {agent.social_linkedin && (
                                        <a
                                            href={agent.social_linkedin}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="block text-sm text-blue-600 hover:underline"
                                        >
                                            LinkedIn
                                        </a>
                                    )}
                                    {agent.social_instagram && (
                                        <a
                                            href={agent.social_instagram}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="block text-sm text-blue-600 hover:underline"
                                        >
                                            Instagram
                                        </a>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle>Timestamps</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Created</label>
                                    <p className="text-sm">{new Date(agent.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Last Updated</label>
                                    <p className="text-sm">{new Date(agent.updated_at).toLocaleDateString()}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
