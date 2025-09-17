import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface Agent {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    bio: string | null;
    avatar: string | null;
    license_number: string | null;
    experience_years: number | null;
    is_active: boolean;
    sort_order: number;
    social_media: {
        facebook?: string;
        twitter?: string;
        linkedin?: string;
        instagram?: string;
    } | null;
}

interface EditAgentData {
    name: string;
    email: string;
    phone: string;
    bio: string;
    license_number: string;
    experience_years: number;
    is_active: boolean;
    sort_order: number;
    facebook: string;
    twitter: string;
    linkedin: string;
    instagram: string;
}

interface AgentsEditProps {
    agent: Agent;
}

export default function AgentsEdit({ agent }: AgentsEditProps) {
    const { data, setData, put, processing, errors } = useForm<EditAgentData>({
        name: agent.name,
        email: agent.email,
        phone: agent.phone || '',
        bio: agent.bio || '',
        license_number: agent.license_number || '',
        experience_years: agent.experience_years || 0,
        is_active: agent.is_active,
        sort_order: agent.sort_order,
        facebook: agent.social_media?.facebook || '',
        twitter: agent.social_media?.twitter || '',
        linkedin: agent.social_media?.linkedin || '',
        instagram: agent.social_media?.instagram || '',
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Agents', href: '/admin/agents' },
        { title: agent.name, href: `/admin/agents/${agent.id}` },
        { title: 'Edit', href: `/admin/agents/${agent.id}/edit` },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/agents/${agent.id}`);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Agent: ${agent.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Agent</h1>
                        <p className="text-muted-foreground">
                            Update the details for {agent.name}
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={`/admin/agents/${agent.id}`}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Agent
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Update the agent's basic details.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">Full Name</Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="e.g. John Smith"
                                            required
                                        />
                                        {errors.name && (
                                            <p className="text-sm text-destructive">{errors.name}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            placeholder="e.g. john@example.com"
                                            required
                                        />
                                        {errors.email && (
                                            <p className="text-sm text-destructive">{errors.email}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="phone">Phone Number</Label>
                                        <Input
                                            id="phone"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            placeholder="e.g. (555) 123-4567"
                                        />
                                        {errors.phone && (
                                            <p className="text-sm text-destructive">{errors.phone}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="license_number">License Number</Label>
                                        <Input
                                            id="license_number"
                                            value={data.license_number}
                                            onChange={(e) => setData('license_number', e.target.value)}
                                            placeholder="e.g. RE123456789"
                                        />
                                        {errors.license_number && (
                                            <p className="text-sm text-destructive">{errors.license_number}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="experience_years">Years of Experience</Label>
                                        <Input
                                            id="experience_years"
                                            type="number"
                                            value={data.experience_years}
                                            onChange={(e) => setData('experience_years', parseInt(e.target.value) || 0)}
                                            placeholder="e.g. 5"
                                            min="0"
                                        />
                                        {errors.experience_years && (
                                            <p className="text-sm text-destructive">{errors.experience_years}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="sort_order">Sort Order</Label>
                                        <Input
                                            id="sort_order"
                                            type="number"
                                            value={data.sort_order}
                                            onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                            placeholder="0"
                                            min="0"
                                        />
                                        {errors.sort_order && (
                                            <p className="text-sm text-destructive">{errors.sort_order}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="bio">Biography</Label>
                                    <Textarea
                                        id="bio"
                                        value={data.bio}
                                        onChange={(e) => setData('bio', e.target.value)}
                                        placeholder="Tell us about the agent's background and expertise..."
                                        rows={4}
                                    />
                                    {errors.bio && (
                                        <p className="text-sm text-destructive">{errors.bio}</p>
                                    )}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Switch
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked)}
                                    />
                                    <Label htmlFor="is_active">Active Agent</Label>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Social Media Links</CardTitle>
                            <CardDescription>
                                Update the agent's social media profiles.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="facebook">Facebook</Label>
                                    <Input
                                        id="facebook"
                                        value={data.facebook}
                                        onChange={(e) => setData('facebook', e.target.value)}
                                        placeholder="https://facebook.com/username"
                                    />
                                    {errors.facebook && (
                                        <p className="text-sm text-destructive">{errors.facebook}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="twitter">Twitter</Label>
                                    <Input
                                        id="twitter"
                                        value={data.twitter}
                                        onChange={(e) => setData('twitter', e.target.value)}
                                        placeholder="https://twitter.com/username"
                                    />
                                    {errors.twitter && (
                                        <p className="text-sm text-destructive">{errors.twitter}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="linkedin">LinkedIn</Label>
                                    <Input
                                        id="linkedin"
                                        value={data.linkedin}
                                        onChange={(e) => setData('linkedin', e.target.value)}
                                        placeholder="https://linkedin.com/in/username"
                                    />
                                    {errors.linkedin && (
                                        <p className="text-sm text-destructive">{errors.linkedin}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="instagram">Instagram</Label>
                                    <Input
                                        id="instagram"
                                        value={data.instagram}
                                        onChange={(e) => setData('instagram', e.target.value)}
                                        placeholder="https://instagram.com/username"
                                    />
                                    {errors.instagram && (
                                        <p className="text-sm text-destructive">{errors.instagram}</p>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center space-x-2">
                        <Button type="submit" disabled={processing} onClick={handleSubmit}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Updating...' : 'Update Agent'}
                        </Button>
                        <Button type="button" variant="outline" asChild>
                            <Link href={`/admin/agents/${agent.id}`}>Cancel</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
